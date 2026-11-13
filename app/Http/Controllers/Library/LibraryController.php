<?php
namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Models\LibraryCopy;
use App\Models\LibraryLoan;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LibraryController extends Controller
{
    private function schoolId(): int { return (int) session('school_id', 1); }

    public function dashboard()
    {
        $school = $this->schoolId();
        $bookCount = LibraryBook::where('school_id',$school)->count();
        $copyCount = LibraryCopy::whereHas('book',fn($q)=>$q->where('school_id',$school))->count();
        $available = LibraryCopy::whereHas('book',fn($q)=>$q->where('school_id',$school))->where('status','available')->count();
        $issued = LibraryLoan::whereHas('copy.book',fn($q)=>$q->where('school_id',$school))->where('status','issued')->count();
        $overdue = LibraryLoan::whereHas('copy.book',fn($q)=>$q->where('school_id',$school))->where('status','issued')->whereDate('due_at','<',today())->count();
        $recentLoans = LibraryLoan::with(['copy.book','student','staff'])->whereHas('copy.book',fn($q)=>$q->where('school_id',$school))->latest()->take(8)->get();
        return view('library.dashboard', compact('bookCount','copyCount','available','issued','overdue','recentLoans'));
    }

    public function index(Request $request)
    {
        $books = LibraryBook::where('school_id',$this->schoolId())
            ->withCount(['copies as total_copies','copies as available_copies'=>fn($q)=>$q->where('status','available')])
            ->when($request->search,fn($q,$s)=>$q->where(fn($w)=>$w->where('title','like',"%$s%")->orWhere('author','like',"%$s%")->orWhere('isbn','like',"%$s%")))
            ->when($request->category,fn($q,$c)=>$q->where('category',$c))
            ->orderBy('title')->paginate(15)->withQueryString();
        $categories = LibraryBook::where('school_id',$this->schoolId())->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        return view('library.books.index',compact('books','categories'));
    }

    public function create() { return view('library.books.form',['book'=>new LibraryBook]); }

    public function store(Request $request)
    {
        $data = $request->validate(['isbn'=>'nullable|string|max:30','title'=>'required|string|max:255','author'=>'nullable|string|max:255','publisher'=>'nullable|string|max:255','category'=>'nullable|string|max:100','edition'=>'nullable|string|max:50','publication_year'=>'nullable|integer|min:1000|max:2100']);
        $data['school_id']=$this->schoolId();
        LibraryBook::create($data);
        return redirect()->route('library.books.index')->with('success','Book added successfully.');
    }

    public function edit(LibraryBook $book)
    {
        $this->guardBook($book);
        return view('library.books.form',compact('book'));
    }

    public function update(Request $request, LibraryBook $book)
    {
        $this->guardBook($book);
        $data=$request->validate(['isbn'=>'nullable|string|max:30','title'=>'required|string|max:255','author'=>'nullable|string|max:255','publisher'=>'nullable|string|max:255','category'=>'nullable|string|max:100','edition'=>'nullable|string|max:50','publication_year'=>'nullable|integer|min:1000|max:2100']);
        $book->update($data);
        return redirect()->route('library.books.index')->with('success','Book updated successfully.');
    }

    public function destroy(LibraryBook $book)
    {
        $this->guardBook($book);
        if($book->copies()->whereHas('loans',fn($q)=>$q->where('status','issued'))->exists()) return back()->withErrors('This book has active loans and cannot be deleted.');
        $book->delete();
        return back()->with('success','Book deleted.');
    }

    public function showBook(LibraryBook $book)
    {
        $this->guardBook($book);
        $book->load(['copies.loans'=>fn($q)=>$q->latest()]);
        return view('library.books.show',compact('book'));
    }

    public function storeCopy(Request $request, LibraryBook $book)
    {
        $this->guardBook($book);
        $data=$request->validate(['accession_number'=>'required|string|max:255|unique:library_copies,accession_number','campus_id'=>'nullable|integer|exists:campuses,id','shelf_location'=>'nullable|string|max:255']);
        $data['library_book_id']=$book->id; $data['status']='available';
        LibraryCopy::create($data);
        return back()->with('success','Copy added.');
    }

    public function updateCopy(Request $request, LibraryCopy $copy)
    {
        $this->guardCopy($copy);
        $data=$request->validate(['shelf_location'=>'nullable|string|max:255','status'=>'required|in:available,damaged,lost,maintenance']);
        if($copy->loans()->where('status','issued')->exists() && $data['status']!=='available') return back()->withErrors('An issued copy cannot be changed to this status.');
        $copy->update($data);
        return back()->with('success','Copy updated.');
    }

    public function loans(Request $request)
    {
        $loans=LibraryLoan::with(['copy.book','student','staff'])->whereHas('copy.book',fn($q)=>$q->where('school_id',$this->schoolId()))
            ->when($request->status,fn($q,$s)=>$q->where('status',$s))
            ->when($request->search,fn($q,$s)=>$q->where(fn($w)=>$w->whereHas('student',fn($x)=>$x->where('first_name','like',"%$s%")->orWhere('last_name','like',"%$s%"))->orWhereHas('staff',fn($x)=>$x->where('first_name','like',"%$s%")->orWhere('last_name','like',"%$s%"))->orWhereHas('copy.book',fn($x)=>$x->where('title','like',"%$s%"))))
            ->latest('issued_at')->paginate(20)->withQueryString();
        return view('library.loans.index',compact('loans'));
    }

    public function issueForm()
    {
        $copies=LibraryCopy::with('book')->where('status','available')->whereHas('book',fn($q)=>$q->where('school_id',$this->schoolId()))->orderBy('accession_number')->get();
        $students=StudentProfile::where('school_id',$this->schoolId())->orderBy('first_name')->get();
        $staff=StaffProfile::where('school_id',$this->schoolId())->where('status','active')->orderBy('first_name')->get();
        return view('library.loans.issue',compact('copies','students','staff'));
    }

    public function issue(Request $request)
    {
        $data=$request->validate(['library_copy_id'=>'required|integer','borrower_type'=>'required|in:student,staff','borrower_id'=>'required|integer','issued_at'=>'required|date','due_at'=>'required|date|after_or_equal:issued_at']);
        DB::transaction(function() use($data) {
            $copy=LibraryCopy::with('book')->lockForUpdate()->findOrFail($data['library_copy_id']);
            $this->guardCopy($copy);
            if($copy->status!=='available') throw ValidationException::withMessages(['library_copy_id'=>'This copy is not available.']);
            $borrower=[];
            if($data['borrower_type']==='student') {
                $person=StudentProfile::where('school_id',$this->schoolId())->findOrFail($data['borrower_id']);
                $borrower=['student_id'=>$person->id,'staff_profile_id'=>null];
            } else {
                $person=StaffProfile::where('school_id',$this->schoolId())->findOrFail($data['borrower_id']);
                $borrower=['student_id'=>null,'staff_profile_id'=>$person->id];
            }
            LibraryLoan::create(array_merge($borrower,['library_copy_id'=>$copy->id,'issued_by'=>auth()->id(),'issued_at'=>$data['issued_at'],'due_at'=>$data['due_at'],'status'=>'issued','fine_amount'=>0]));
            $copy->update(['status'=>'issued']);
        });
        return redirect()->route('library.loans.index')->with('success','Book issued successfully.');
    }

    public function returnBook(Request $request, LibraryLoan $loan)
    {
        $this->guardLoan($loan);
        if($loan->status!=='issued') return back()->withErrors('This loan is already closed.');
        $request->validate(['returned_at'=>'nullable|date']);
        DB::transaction(function() use($loan,$request) {
            $locked=LibraryLoan::lockForUpdate()->findOrFail($loan->id);
            if($locked->status!=='issued') throw ValidationException::withMessages(['loan'=>'Loan already returned.']);
            $returned= $request->input('returned_at') ? \Illuminate\Support\Carbon::parse($request->input('returned_at')) : today();
            $days=max(0,$returned->startOfDay()->diffInDays($locked->due_at->startOfDay(), false)*-1);
            $fine=$days*5;
            $locked->update(['returned_at'=>$returned->toDateString(),'received_by'=>auth()->id(),'fine_amount'=>$fine,'status'=>'returned']);
            $locked->copy()->update(['status'=>'available']);
        });
        return back()->with('success','Book returned. Applicable fine has been calculated.');
    }

    private function guardBook(LibraryBook $book): void { abort_unless($book->school_id===$this->schoolId(),404); }
    private function guardCopy(LibraryCopy $copy): void { $copy->loadMissing('book'); $this->guardBook($copy->book); }
    private function guardLoan(LibraryLoan $loan): void { $loan->loadMissing('copy.book'); $this->guardBook($loan->copy->book); }
}

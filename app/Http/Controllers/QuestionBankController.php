<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('questions as q')
            ->join('question_banks as qb', 'qb.id', '=', 'q.question_bank_id')
            ->join('subjects as s', 's.id', '=', 'q.subject_id')
            ->select('q.*', 'qb.name as bank_name', 's.name as subject_name');
        $query->when($request->filled('search'), fn($q) => $q->where('q.question_text', 'like', '%' . $request->search . '%'));
        $query->when($request->filled('subject_id'), fn($q) => $q->where('q.subject_id', $request->subject_id));
        $query->when($request->filled('question_type'), fn($q) => $q->where('q.question_type', $request->question_type));
        $query->when($request->filled('difficulty'), fn($q) => $q->where('q.difficulty', $request->difficulty));
        $questions = $query->orderByDesc('q.created_at')->paginate(10)->withQueryString();
        $subjects = DB::table('subjects')->where('is_active', 1)->orderBy('name')->get();
        $stats = [
            'total' => DB::table('questions')->count(),
            'mcq' => DB::table('questions')->where('question_type', 'mcq')->count(),
            'descriptive' => DB::table('questions')->where('question_type', 'descriptive')->count(),
            'active' => DB::table('questions')->where('is_active', 1)->count(),
        ];
        return view('question-bank.index', compact('questions', 'subjects', 'stats'));
    }

    public function create()
    {
        return view('question-bank.form', $this->formData() + [
            'question' => null, 'options' => collect(), 'pageTitle' => 'Add Question',
            'formAction' => route('question-bank.store'), 'formMethod' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        [$data, $options] = $this->validated($request);
        $id = DB::table('questions')->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
        $this->saveOptions($id, $options);
        return redirect()->route('question-bank.show', $id)->with('success', 'Question added successfully.');
    }

    public function show(string $id)
    {
        $question = DB::table('questions as q')->join('question_banks as qb', 'qb.id', '=', 'q.question_bank_id')
            ->join('subjects as s', 's.id', '=', 'q.subject_id')
            ->select('q.*', 'qb.name as bank_name', 's.name as subject_name')->where('q.id', $id)->first();
        abort_unless($question, 404);
        $options = DB::table('question_options')->where('question_id', $id)->orderBy('sequence')->get();
        return view('question-bank.show', compact('question', 'options'));
    }

    public function edit(string $id)
    {
        $question = DB::table('questions')->where('id', $id)->first();
        abort_unless($question, 404);
        $options = DB::table('question_options')->where('question_id', $id)->orderBy('sequence')->get();
        return view('question-bank.form', $this->formData() + [
            'question' => $question, 'options' => $options, 'pageTitle' => 'Edit Question',
            'formAction' => route('question-bank.update', $id), 'formMethod' => 'PUT',
        ]);
    }

    public function update(Request $request, string $id)
    {
        abort_unless(DB::table('questions')->where('id', $id)->exists(), 404);
        [$data, $options] = $this->validated($request);
        DB::table('questions')->where('id', $id)->update($data + ['updated_at' => now()]);
        DB::table('question_options')->where('question_id', $id)->delete();
        $this->saveOptions((int)$id, $options);
        return redirect()->route('question-bank.show', $id)->with('success', 'Question updated successfully.');
    }

    public function destroy(string $id)
    {
        DB::table('questions')->where('id', $id)->delete();
        return redirect()->route('question-bank.index')->with('success', 'Question deleted successfully.');
    }

    public function toggle(string $id)
    {
        $question = DB::table('questions')->where('id', $id)->first();
        abort_unless($question, 404);
        DB::table('questions')->where('id', $id)->update([
            'is_active' => !$question->is_active, 'updated_at' => now(),
        ]);
        return back()->with('success', 'Question status updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question_bank_id' => ['required', 'exists:question_banks,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'chapter_id' => ['nullable', 'exists:curriculum_chapters,id'],
            'topic_id' => ['nullable', 'exists:curriculum_topics,id'],
            'question_type' => ['required', Rule::in(['mcq', 'true_false', 'descriptive'])],
            'question_text' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'marks' => ['required', 'numeric', 'min:0.5'],
            'answer_text' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'options' => ['nullable', 'array'],
            'options.*.option_label' => ['required_with:options', 'string', 'max:5'],
            'options.*.option_text' => ['required_with:options', 'string'],
            'options.*.is_correct' => ['nullable', 'boolean'],
        ]);
        $options = $data['options'] ?? [];
        unset($data['options']);
        return [$data, $options];
    }

    private function saveOptions(int $id, array $options): void
    {
        foreach ($options as $index => $option) {
            DB::table('question_options')->insert([
                'question_id' => $id,
                'option_label' => $option['option_label'],
                'option_text' => $option['option_text'],
                'is_correct' => !empty($option['is_correct']),
                'sequence' => $index + 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function formData(): array
    {
        return [
            'banks' => DB::table('question_banks')->where('is_active', 1)->orderBy('name')->get(),
            'subjects' => DB::table('subjects')->where('is_active', 1)->orderBy('name')->get(),
            'chapters' => DB::table('curriculum_chapters')->orderBy('title')->get(),
            'topics' => DB::table('curriculum_topics')->orderBy('title')->get(),
        ];
    }
}

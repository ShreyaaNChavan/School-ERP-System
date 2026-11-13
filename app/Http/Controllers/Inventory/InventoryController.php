<?php
namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryVendor;
use App\Models\InventoryAsset;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    private function schoolId(): int { return (int) session('school_id', 1); }

    public function dashboard()
    {
        $school=$this->schoolId();
        $itemCount=InventoryItem::where('school_id',$school)->count();
        $stockValue=(float)InventoryItem::where('school_id',$school)->selectRaw('COALESCE(SUM(quantity_on_hand * unit_cost),0) as total')->value('total');
        $lowStock=InventoryItem::where('school_id',$school)->whereColumn('quantity_on_hand','<=','reorder_level')->count();
        $vendorCount=InventoryVendor::where('school_id',$school)->count();
        $assetCount=InventoryAsset::where('school_id',$school)->count();
        $pendingOrders=PurchaseOrder::where('school_id',$school)->whereIn('status',['draft','pending','approved','ordered'])->count();
        $recentItems=InventoryItem::with('category')->where('school_id',$school)->latest()->take(8)->get();
        $lowStockItems=InventoryItem::where('school_id',$school)->whereColumn('quantity_on_hand','<=','reorder_level')->orderBy('quantity_on_hand')->take(8)->get();
        return view('inventory.dashboard',compact('itemCount','stockValue','lowStock','vendorCount','assetCount','pendingOrders','recentItems','lowStockItems'));
    }

    public function index(Request $request)
    {
        $items=InventoryItem::with('category')->where('school_id',$this->schoolId())
            ->when($request->search,fn($q,$s)=>$q->where(fn($w)=>$w->where('name','like',"%$s%")->orWhere('item_code','like',"%$s%")))
            ->when($request->category_id,fn($q,$c)=>$q->where('inventory_category_id',$c))
            ->when($request->stock==='low',fn($q)=>$q->whereColumn('quantity_on_hand','<=','reorder_level'))
            ->orderBy('name')->paginate(15)->withQueryString();
        $categories=InventoryCategory::where('school_id',$this->schoolId())->orderBy('name')->get();
        return view('inventory.items.index',compact('items','categories'));
    }

    public function create()
    {
        $categories=InventoryCategory::where('school_id',$this->schoolId())->orderBy('name')->get();
        return view('inventory.items.form',['item'=>new InventoryItem,'categories'=>$categories]);
    }

    public function store(Request $request)
    {
        $data=$request->validate(['inventory_category_id'=>'nullable|integer','name'=>'required|string|max:255','item_code'=>'required|string|max:255','unit'=>'required|string|max:30','quantity_on_hand'=>'required|numeric|min:0','reorder_level'=>'required|numeric|min:0','unit_cost'=>'required|numeric|min:0']);
        $data['school_id']=$this->schoolId();
        $request->validate(['item_code'=>'unique:inventory_items,item_code,NULL,id,school_id,'.$this->schoolId()]);
        $this->validateCategory($data['inventory_category_id']??null);
        InventoryItem::create($data);
        return redirect()->route('inventory.items.index')->with('success','Inventory item added.');
    }

    public function edit(InventoryItem $item)
    {
        $this->guardItem($item); $categories=InventoryCategory::where('school_id',$this->schoolId())->orderBy('name')->get();
        return view('inventory.items.form',compact('item','categories'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $this->guardItem($item);
        $data=$request->validate(['inventory_category_id'=>'nullable|integer','name'=>'required|string|max:255','item_code'=>'required|string|max:255','unit'=>'required|string|max:30','quantity_on_hand'=>'required|numeric|min:0','reorder_level'=>'required|numeric|min:0','unit_cost'=>'required|numeric|min:0']);
        $request->validate(['item_code'=>'unique:inventory_items,item_code,'.$item->id.',id,school_id,'.$this->schoolId()]);
        $this->validateCategory($data['inventory_category_id']??null);
        $item->update($data);
        return redirect()->route('inventory.items.index')->with('success','Inventory item updated.');
    }

    public function destroy(InventoryItem $item)
    {
        $this->guardItem($item);
        if(PurchaseOrderItem::where('inventory_item_id',$item->id)->exists()) return back()->withErrors('This item is referenced by purchase orders and cannot be deleted.');
        $item->delete(); return back()->with('success','Item deleted.');
    }

    public function showItem(InventoryItem $item) { $this->guardItem($item); $item->load('category'); return view('inventory.items.show',compact('item')); }

    public function categories()
    {
        $categories=InventoryCategory::withCount('items')->where('school_id',$this->schoolId())->orderBy('name')->paginate(15);
        return view('inventory.categories.index',compact('categories'));
    }
    public function storeCategory(Request $r)
    {
        $d=$r->validate(['name'=>'required|string|max:255','description'=>'nullable|string']);
        $d['school_id']=$this->schoolId();
        InventoryCategory::create($d); return back()->with('success','Category created.');
    }
    public function updateCategory(Request $r,InventoryCategory $category)
    {
        $this->guardCategory($category); $d=$r->validate(['name'=>'required|string|max:255','description'=>'nullable|string']);
        $category->update($d); return back()->with('success','Category updated.');
    }
    public function destroyCategory(InventoryCategory $category)
    {
        $this->guardCategory($category);
        if($category->items()->exists() || InventoryAsset::where('inventory_category_id',$category->id)->exists()) return back()->withErrors('Category is in use.');
        $category->delete(); return back()->with('success','Category deleted.');
    }

    public function vendors()
    {
        $vendors=InventoryVendor::where('school_id',$this->schoolId())->orderBy('name')->paginate(15);
        return view('inventory.vendors.index',compact('vendors'));
    }
    public function storeVendor(Request $r)
    {
        $d=$r->validate(['name'=>'required|string|max:255','contact_person'=>'nullable|string|max:255','phone'=>'nullable|string|max:30','email'=>'nullable|email|max:255','address'=>'nullable|string','tax_identifier'=>'nullable|string|max:50']);
        $d['school_id']=$this->schoolId(); InventoryVendor::create($d); return back()->with('success','Vendor added.');
    }
    public function updateVendor(Request $r,InventoryVendor $vendor)
    {
        $this->guardVendor($vendor); $d=$r->validate(['name'=>'required|string|max:255','contact_person'=>'nullable|string|max:255','phone'=>'nullable|string|max:30','email'=>'nullable|email|max:255','address'=>'nullable|string','tax_identifier'=>'nullable|string|max:50']);
        $vendor->update($d); return back()->with('success','Vendor updated.');
    }
    public function destroyVendor(InventoryVendor $vendor)
    {
        $this->guardVendor($vendor);
        if(PurchaseOrder::where('vendor_id',$vendor->id)->exists()) return back()->withErrors('Vendor is referenced by purchase orders.');
        $vendor->delete(); return back()->with('success','Vendor deleted.');
    }

    public function assets()
    {
        $assets=InventoryAsset::with('category')->where('school_id',$this->schoolId())->latest()->paginate(15);
        return view('inventory.assets.index',compact('assets'));
    }
    public function createAsset()
    {
        $categories=InventoryCategory::where('school_id',$this->schoolId())->orderBy('name')->get();
        return view('inventory.assets.form',['asset'=>new InventoryAsset,'categories'=>$categories]);
    }
    public function storeAsset(Request $r)
    {
        $d=$r->validate(['campus_id'=>'nullable|integer|exists:campuses,id','inventory_category_id'=>'nullable|integer','asset_tag'=>'required|string|max:255|unique:asset_register,asset_tag','name'=>'required|string|max:255','serial_number'=>'nullable|string|max:255','purchase_date'=>'nullable|date','purchase_cost'=>'required|numeric|min:0','condition'=>'required|in:excellent,good,fair,poor,damaged','location'=>'nullable|string|max:255','status'=>'required|in:active,under_repair,retired,disposed']);
        $this->validateCategory($d['inventory_category_id']??null); $d['school_id']=$this->schoolId(); InventoryAsset::create($d);
        return redirect()->route('inventory.assets.index')->with('success','Asset registered.');
    }
    public function editAsset(InventoryAsset $asset)
    {
        $this->guardAsset($asset); $categories=InventoryCategory::where('school_id',$this->schoolId())->orderBy('name')->get();
        return view('inventory.assets.form',compact('asset','categories'));
    }
    public function updateAsset(Request $r,InventoryAsset $asset)
    {
        $this->guardAsset($asset);
        $d=$r->validate(['campus_id'=>'nullable|integer|exists:campuses,id','inventory_category_id'=>'nullable|integer','asset_tag'=>'required|string|max:255|unique:asset_register,asset_tag,'.$asset->id,'name'=>'required|string|max:255','serial_number'=>'nullable|string|max:255','purchase_date'=>'nullable|date','purchase_cost'=>'required|numeric|min:0','condition'=>'required|in:excellent,good,fair,poor,damaged','location'=>'nullable|string|max:255','status'=>'required|in:active,under_repair,retired,disposed']);
        $this->validateCategory($d['inventory_category_id']??null); $asset->update($d); return redirect()->route('inventory.assets.index')->with('success','Asset updated.');
    }
    public function destroyAsset(InventoryAsset $asset)
    {
        $this->guardAsset($asset); $asset->delete(); return back()->with('success','Asset removed.');
    }

    public function purchaseOrders()
    {
        $orders=PurchaseOrder::with('vendor')->where('school_id',$this->schoolId())->latest('order_date')->paginate(15);
        return view('inventory.orders.index',compact('orders'));
    }
    public function createPurchaseOrder()
    {
        $vendors=InventoryVendor::where('school_id',$this->schoolId())->orderBy('name')->get();
        $items=InventoryItem::where('school_id',$this->schoolId())->orderBy('name')->get();
        return view('inventory.orders.create',compact('vendors','items'));
    }
    public function storePurchaseOrder(Request $r)
    {
        $d=$r->validate(['vendor_id'=>'required|integer','order_number'=>'required|string|max:255|unique:purchase_orders,order_number','order_date'=>'required|date','expected_delivery_date'=>'nullable|date|after_or_equal:order_date','tax_amount'=>'nullable|numeric|min:0','items'=>'required|array|min:1','items.*.inventory_item_id'=>'required|integer','items.*.quantity'=>'required|numeric|min:0.01','items.*.unit_cost'=>'required|numeric|min:0']);
        $vendor=InventoryVendor::where('school_id',$this->schoolId())->findOrFail($d['vendor_id']);
        $subtotal=0; foreach($d['items'] as $line){ InventoryItem::where('school_id',$this->schoolId())->findOrFail($line['inventory_item_id']); $subtotal += $line['quantity']*$line['unit_cost']; }
        $tax=(float)($d['tax_amount']??0);
        DB::transaction(function() use($d,$subtotal,$tax) {
            $order=PurchaseOrder::create(['school_id'=>$this->schoolId(),'vendor_id'=>$d['vendor_id'],'order_number'=>$d['order_number'],'order_date'=>$d['order_date'],'expected_delivery_date'=>$d['expected_delivery_date']??null,'subtotal'=>$subtotal,'tax_amount'=>$tax,'total_amount'=>$subtotal+$tax,'status'=>'draft','created_by'=>auth()->id()]);
            foreach($d['items'] as $line) $order->items()->create(['inventory_item_id'=>$line['inventory_item_id'],'quantity'=>$line['quantity'],'unit_cost'=>$line['unit_cost'],'total_cost'=>$line['quantity']*$line['unit_cost']]);
        });
        return redirect()->route('inventory.purchase-orders.index')->with('success','Purchase order created.');
    }
    public function showPurchaseOrder(PurchaseOrder $order)
    {
        $this->guardOrder($order); $order->load(['vendor','items.item']); return view('inventory.orders.show',compact('order'));
    }
    public function updatePurchaseOrderStatus(Request $r,PurchaseOrder $order)
    {
        $this->guardOrder($order); $d=$r->validate(['status'=>'required|in:pending,approved,ordered,received,cancelled']);
        DB::transaction(function() use($order,$d) {
            $locked=PurchaseOrder::with('items')->lockForUpdate()->findOrFail($order->id);
            if($locked->status==='received') throw ValidationException::withMessages(['status'=>'Received purchase orders cannot be changed.']);
            if($d['status']==='received') {
                if(in_array($locked->status,['cancelled','draft'],true)) throw ValidationException::withMessages(['status'=>'Approve/order the purchase order before receiving it.']);
                foreach($locked->items as $line) {
                    $item=InventoryItem::where('school_id',$this->schoolId())->lockForUpdate()->findOrFail($line->inventory_item_id);
                    $item->increment('quantity_on_hand',$line->quantity);
                    $item->update(['unit_cost'=>$line->unit_cost]);
                }
            }
            if($locked->status==='received' && $d['status']!=='received') throw ValidationException::withMessages(['status'=>'A received order cannot be reversed here.']);
            $locked->update(['status'=>$d['status']]);
        });
        return back()->with('success','Purchase order status updated.');
    }

    private function validateCategory($id): void { if($id) InventoryCategory::where('school_id',$this->schoolId())->findOrFail($id); }
    private function guardItem(InventoryItem $m): void { abort_unless($m->school_id===$this->schoolId(),404); }
    private function guardCategory(InventoryCategory $m): void { abort_unless($m->school_id===$this->schoolId(),404); }
    private function guardVendor(InventoryVendor $m): void { abort_unless($m->school_id===$this->schoolId(),404); }
    private function guardAsset(InventoryAsset $m): void { abort_unless($m->school_id===$this->schoolId(),404); }
    private function guardOrder(PurchaseOrder $m): void { abort_unless($m->school_id===$this->schoolId(),404); }
}

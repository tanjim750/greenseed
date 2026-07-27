<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index()
    {
        $statuses = OrderStatus::orderBy('sort_order')->orderBy('name')->get();

        return view('backend.order_statuses.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['slug'] = OrderStatus::makeSlug($data['name']);
        $data = $this->applyBehaviorPreset($data);

        OrderStatus::create($data);

        return redirect()->route('admin.order-statuses.index')->with('success', 'Order status created successfully.');
    }

    public function update(Request $request, OrderStatus $orderStatus)
    {
        $data = $this->validatedData($request, $orderStatus->id);
        $data['slug'] = OrderStatus::makeSlug($data['name']);
        $data = $this->applyBehaviorPreset($data);

        if ($orderStatus->is_default) {
            $data['is_default'] = true;
            $data['name'] = $orderStatus->name;
            $data['slug'] = $orderStatus->slug;
        }

        $orderStatus->update($data);

        return redirect()->route('admin.order-statuses.index')->with('success', 'Order status updated successfully.');
    }

    public function destroy(OrderStatus $orderStatus)
    {
        if ($orderStatus->is_default) {
            return redirect()->route('admin.order-statuses.index')->with('error', 'Default statuses cannot be deleted.');
        }

        $orderStatus->delete();

        return redirect()->route('admin.order-statuses.index')->with('success', 'Order status deleted successfully.');
    }

    private function validatedData(Request $request, $ignoreId = null): array
    {
        $unique = 'unique:order_statuses,name';
        if ($ignoreId) {
            $unique .= ',' . $ignoreId;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:50', $unique],
            'status_group' => ['required', 'in:active,delivered,cancelled,return,custom'],
            'badge_class' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_:\\-\\s]+$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'marks_payment_paid' => ['nullable', 'boolean'],
            'restores_stock' => ['nullable', 'boolean'],
            'reduces_stock' => ['nullable', 'boolean'],
            'sms_key' => ['nullable', 'string', 'max:50'],
        ]);
    }

    private function applyBehaviorPreset(array $data): array
    {
        $group = $data['status_group'];

        $data['badge_class'] = $data['badge_class'] ?: $this->defaultBadgeClass($group);
        $data['sort_order'] = $data['sort_order'] ?? 999;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['is_default'] = $data['is_default'] ?? false;

        $data['counts_as_active'] = in_array($group, ['active', 'delivered'], true);
        $data['counts_as_delivered'] = $group === 'delivered';
        $data['counts_as_cancelled'] = $group === 'cancelled';
        $data['counts_as_return'] = $group === 'return';
        $data['counts_as_shipped'] = false;

        $data['marks_payment_paid'] = (bool) ($data['marks_payment_paid'] ?? false) || $group === 'delivered';
        $data['restores_stock'] = (bool) ($data['restores_stock'] ?? false) || in_array($group, ['cancelled', 'return'], true);
        $data['reduces_stock'] = (bool) ($data['reduces_stock'] ?? false) || in_array($group, ['active', 'delivered'], true);

        if ($data['restores_stock'] && $data['reduces_stock']) {
            $data['reduces_stock'] = false;
        }

        return $data;
    }

    private function defaultBadgeClass(string $group): string
    {
        return [
            'active' => 'bg-info text-white',
            'delivered' => 'bg-success',
            'cancelled' => 'bg-danger',
            'return' => 'bg-danger',
            'custom' => 'bg-secondary',
        ][$group] ?? 'bg-secondary';
    }
}

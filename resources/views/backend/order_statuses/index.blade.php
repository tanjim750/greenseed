@extends('backend.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Order Statuses</li>
                </ol>
            </div>
            <h4 class="page-title">Order Statuses</h4>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Add Status</h5>
                <form action="{{ route('admin.order-statuses.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="50">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Behavior</label>
                        <select name="status_group" class="form-control">
                            <option value="active">Active / Processing</option>
                            <option value="delivered">Delivered / Successful</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="return">Return</option>
                            <option value="custom">Custom Display Only</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Badge Class</label>
                        <input type="text" name="badge_class" class="form-control" value="{{ old('badge_class') }}" placeholder="bg-secondary">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 999) }}" min="0">
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="new_is_active" value="1" checked>
                        <label class="form-check-label" for="new_is_active">Show in dropdowns</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="marks_payment_paid" value="0">
                        <input class="form-check-input" type="checkbox" name="marks_payment_paid" id="new_marks_payment_paid" value="1">
                        <label class="form-check-label" for="new_marks_payment_paid">Mark payment paid</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="reduces_stock" value="0">
                        <input class="form-check-input" type="checkbox" name="reduces_stock" id="new_reduces_stock" value="1">
                        <label class="form-check-label" for="new_reduces_stock">Keep stock reserved</label>
                    </div>
                    <div class="form-check mb-3">
                        <input type="hidden" name="restores_stock" value="0">
                        <input class="form-check-input" type="checkbox" name="restores_stock" id="new_restores_stock" value="1">
                        <label class="form-check-label" for="new_restores_stock">Restore stock on transition</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SMS Key</label>
                        <select name="sms_key" class="form-control">
                            <option value="">No SMS mapping</option>
                            @foreach(['pending','incomplete','on hold','scheduled','confirmed','cancelled','processing','courier complete','shipped','delivered','returning','return received','return missing'] as $smsKey)
                                <option value="{{ $smsKey }}">{{ ucwords($smsKey) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Status</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-centered table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Behavior</th>
                                <th>Effects</th>
                                <th>Sort</th>
                                <th style="width: 170px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($statuses as $status)
                            <tr>
                                <td>
                                    <span class="badge {{ $status->badge_class }}">{{ $status->name }}</span>
                                    @if($status->is_default)
                                        <span class="badge bg-light text-dark border">Default</span>
                                    @endif
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $status->status_group)) }}</td>
                                <td>
                                    @if($status->marks_payment_paid)<span class="badge bg-success">Paid</span>@endif
                                    @if($status->reduces_stock)<span class="badge bg-info text-white">Reserved</span>@endif
                                    @if($status->restores_stock)<span class="badge bg-danger">Restores</span>@endif
                                    @if(!$status->is_active)<span class="badge bg-secondary">Hidden</span>@endif
                                </td>
                                <td>{{ $status->sort_order }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit-status-{{ $status->id }}">Edit</button>
                                    @unless($status->is_default)
                                        <form action="{{ route('admin.order-statuses.destroy', $status) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this status? Existing orders keep their current text value.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                            <tr class="collapse" id="edit-status-{{ $status->id }}">
                                <td colspan="5">
                                    <form action="{{ route('admin.order-statuses.update', $status) }}" method="POST" class="row g-2 align-items-end">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-md-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $status->name }}" {{ $status->is_default ? 'readonly' : '' }} required maxlength="50">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Behavior</label>
                                            <select name="status_group" class="form-control">
                                                @foreach(['active' => 'Active', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled', 'return' => 'Return', 'custom' => 'Custom'] as $group => $label)
                                                    <option value="{{ $group }}" {{ $status->status_group === $group ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Badge</label>
                                            <input type="text" name="badge_class" class="form-control" value="{{ $status->badge_class }}">
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">Sort</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $status->sort_order }}" min="0">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="hidden" name="marks_payment_paid" value="0">
                                            <input type="hidden" name="reduces_stock" value="0">
                                            <input type="hidden" name="restores_stock" value="0">
                                            <label class="me-2"><input type="checkbox" name="is_active" value="1" {{ $status->is_active ? 'checked' : '' }}> Show</label>
                                            <label class="me-2"><input type="checkbox" name="marks_payment_paid" value="1" {{ $status->marks_payment_paid ? 'checked' : '' }}> Paid</label>
                                            <label class="me-2"><input type="checkbox" name="reduces_stock" value="1" {{ $status->reduces_stock ? 'checked' : '' }}> Reserved</label>
                                            <label class="me-2"><input type="checkbox" name="restores_stock" value="1" {{ $status->restores_stock ? 'checked' : '' }}> Restore</label>
                                            <select name="sms_key" class="form-control mt-2">
                                                <option value="">No SMS mapping</option>
                                                @foreach(['pending','incomplete','on hold','scheduled','confirmed','cancelled','processing','courier complete','shipped','delivered','returning','return received','return missing'] as $smsKey)
                                                    <option value="{{ $smsKey }}" {{ $status->sms_key === $smsKey ? 'selected' : '' }}>{{ ucwords($smsKey) }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary mt-2">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

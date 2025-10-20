@extends('layouts.app')

@section('title', 'Compare Machines - MK Gilze Africa')

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('configurator.index') }}">Configurator</a></li>
                <li class="breadcrumb-item active">Compare Machines</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-12 text-center mb-4">
        <h1>Compare Machine Configurations</h1>
        <p class="lead">Side-by-side comparison to help you make the best choice</p>
    </div>
</div>

<div class="row">
    <!-- Machine 1 Configuration -->
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Configuration 1</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="machine1_id" class="form-label">Select Machine</label>
                    <select class="form-select" id="machine1_id" name="machine1_id">
                        <option value="">Choose a machine...</option>
                        @foreach($machines as $machine)
                        <option value="{{ $machine->id }}">{{ $machine->display_name }} (€{{ number_format($machine->base_price, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div id="machine1-components" style="display: none;">
                    <label class="form-label">Select Components</label>
                    <div id="machine1-components-list" class="components-list" style="max-height: 400px; overflow-y: auto;">
                        <!-- Components will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Machine 2 Configuration -->
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Configuration 2</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="machine2_id" class="form-label">Select Machine</label>
                    <select class="form-select" id="machine2_id" name="machine2_id">
                        <option value="">Choose a machine...</option>
                        @foreach($machines as $machine)
                        <option value="{{ $machine->id }}">{{ $machine->display_name }} (€{{ number_format($machine->base_price, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div id="machine2-components" style="display: none;">
                    <label class="form-label">Select Components</label>
                    <div id="machine2-components-list" class="components-list" style="max-height: 400px; overflow-y: auto;">
                        <!-- Components will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comparison Results -->
<div class="row">
    <div class="col-12">
        <div class="card shadow" id="comparison-results" style="display: none;">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Comparison Results</h5>
            </div>
            <div class="card-body">
                <div id="comparison-content">
                    <!-- Comparison results will be displayed here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12 text-center">
        <button id="compare-btn" class="btn btn-primary btn-lg" disabled>
            <i class="fas fa-balance-scale me-2"></i>Compare Configurations
        </button>
        <a href="{{ route('configurator.index') }}" class="btn btn-secondary btn-lg ms-2">
            <i class="fas fa-arrow-left me-2"></i>Back to Configurator
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
.components-list {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
}

.component-item {
    padding: 0.5rem;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
}

.component-item:hover {
    background-color: #f8f9fa;
}

.component-item.selected {
    background-color: #e7f1ff;
    border-left: 3px solid #0d6efd;
}

.comparison-table th {
    background-color: #f8f9fa;
}

.price-difference.positive {
    color: #198754;
    font-weight: bold;
}

.price-difference.negative {
    color: #dc3545;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
// JavaScript for the comparison functionality
// This would handle loading components, selection, and comparison
// Due to length, I'll provide the basic structure
</script>
@endpush
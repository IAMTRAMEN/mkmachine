@extends('layouts.app')

@section('title', 'Machine Configurator - MK Gilze Africa')

@section('content')
<div class="row">
    <div class="col-12 text-center mb-5">
        <h1 class="display-4">Machine Configurator</h1>
        <p class="lead">Design your perfect packaging solution with our interactive configurator</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-cogs fa-2x text-primary mb-2"></i>
                        <h5>Configure</h5>
                        <p class="small text-muted">Build your custom machine from scratch</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-exchange-alt fa-2x text-success mb-2"></i>
                        <h5>Compare</h5>
                        <p class="small text-muted">Compare different configurations side by side</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-save fa-2x text-info mb-2"></i>
                        <h5>Save & Share</h5>
                        <p class="small text-muted">Save your configurations for later use</p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('configurator.compare') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-exchange-alt me-2"></i>Compare Machines
                    </a>
                    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#loadConfigurationModal">
                        <i class="fas fa-folder-open me-2"></i>Load Saved Configuration
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Machines -->
@if($featuredMachines->count() > 0)
<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-4">Featured Machines</h2>
        <div class="row justify-content-center">
            @foreach($featuredMachines as $machine)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm featured-machine">
                    @if($machine->image_url)
                    <img src="{{ $machine->image_url }}" class="card-img-top" alt="{{ $machine->display_name }}" style="height: 200px; object-fit: cover;">
                    @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-cogs fa-4x text-muted"></i>
                    </div>
                    @endif
                    
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title">{{ $machine->display_name }}</h5>
                            <span class="badge bg-warning">Featured</span>
                        </div>
                        <h6 class="card-subtitle mb-2 text-muted">Model: {{ $machine->name }}</h6>
                        <p class="card-text flex-grow-1">{{ Str::limit($machine->description, 100) }}</p>
                        
                        @if($machine->specifications)
                        <div class="machine-specs mb-3">
                            <strong>Key Features:</strong>
                            <ul class="list-unstyled mb-0">
                                @foreach(array_slice($machine->specifications, 0, 2) as $key => $value)
                                <li><small>{{ $key }}: {{ $value }}</small></li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h4 text-primary mb-0">€{{ number_format($machine->base_price, 2) }}</span>
                                <span class="badge bg-success">Best Value</span>
                            </div>
                            
                            <a href="{{ route('configurator.show', $machine->id) }}" class="btn btn-primary w-100">
                                <i class="fas fa-cog me-2"></i>Configure Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- All Machines -->
@if($allMachines->count() > 0)
<div class="row">
    <div class="col-12">
        <h2 class="text-center mb-4">All Available Machines</h2>
        <div class="row">
            @foreach($allMachines as $machine)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    @if($machine->image_url)
                    <img src="{{ $machine->image_url }}" class="card-img-top" alt="{{ $machine->display_name }}" style="height: 180px; object-fit: cover;">
                    @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                        <i class="fas fa-cogs fa-3x text-muted"></i>
                    </div>
                    @endif
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $machine->display_name }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Model: {{ $machine->name }}</h6>
                        <p class="card-text flex-grow-1">{{ Str::limit($machine->description, 80) }}</p>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h5 text-primary mb-0">€{{ number_format($machine->base_price, 2) }}</span>
                            </div>
                            
                            <a href="{{ route('configurator.show', $machine->id) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-cog me-2"></i>Configure
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Load Configuration Modal -->
<div class="modal fade" id="loadConfigurationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Load Saved Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="load-configuration-form">
                    <div class="mb-3">
                        <label for="configuration_number" class="form-label">Configuration Number</label>
                        <input type="text" class="form-control" id="configuration_number" 
                               placeholder="Enter your configuration number (e.g., CFG20240120001)" required>
                        <div class="form-text">Enter the configuration number you received when saving your configuration.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="loadConfiguration()">
                    <i class="fas fa-folder-open me-2"></i>Load Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <h3 class="text-center mb-4">How It Works</h3>
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="step-number">1</div>
                        <i class="fas fa-mouse-pointer fa-2x text-primary mb-2"></i>
                        <h5>Select Machine</h5>
                        <p class="small">Choose from our range of packaging machines</p>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="step-number">2</div>
                        <i class="fas fa-sliders-h fa-2x text-primary mb-2"></i>
                        <h5>Customize</h5>
                        <p class="small">Add components and features that match your needs</p>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="step-number">3</div>
                        <i class="fas fa-calculator fa-2x text-primary mb-2"></i>
                        <h5>Get Instant Quote</h5>
                        <p class="small">See real-time pricing as you configure</p>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="step-number">4</div>
                        <i class="fas fa-download fa-2x text-primary mb-2"></i>
                        <h5>Save & Download</h5>
                        <p class="small">Save your configuration and download PDF quote</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.featured-machine {
    border: 2px solid #ffc107;
    transform: scale(1.02);
    transition: transform 0.3s ease;
}

.featured-machine:hover {
    transform: scale(1.05);
}

.step-number {
    width: 40px;
    height: 40px;
    background: #0d6efd;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-weight: bold;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>
@endpush

@push('scripts')
<script>
function loadConfiguration() {
    const configNumber = document.getElementById('configuration_number').value.trim();
    if (!configNumber) {
        alert('Please enter a configuration number');
        return;
    }

    window.location.href = '/configurator/load/' + configNumber;
}

// Close modal on successful load
document.getElementById('configuration_number').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        loadConfiguration();
    }
});
</script>
@endpush
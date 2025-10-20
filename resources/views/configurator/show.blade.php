@extends('layouts.app')

@section('title', "Configure {$machine->display_name} - MK Gilze Africa")

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('configurator.index') }}">Machines</a></li>
                <li class="breadcrumb-item active">{{ $machine->display_name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Machine Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h2 class="card-title">{{ $machine->display_name }}</h2>
                        <p class="card-text">{{ $machine->description }}</p>
                        
                        @if($machine->specifications)
                        <div class="mt-3">
                            <h6>Specifications:</h6>
                            <div class="row">
                                @foreach($machine->specifications as $key => $value)
                                <div class="col-sm-6">
                                    <small><strong>{{ $key }}:</strong> {{ $value }}</small>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h3 text-primary">€{{ number_format($machine->base_price, 2) }}</div>
                        <span class="badge bg-secondary">Base Machine</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compatibility Alerts -->
        <div id="compatibility-alerts" class="mb-4"></div>
<!-- Popular Components Section -->
@if($popularComponents->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Recommended Components</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">These components work well with the {{ $machine->display_name }}:</p>
        <div class="row">
            @foreach($popularComponents as $component)
            <div class="col-md-4 mb-3">
                <div class="card popular-component-card" data-component-id="{{ $component->id }}">
                    <div class="card-body">
                        <h6 class="card-title">{{ $component->name }}</h6>
                        <p class="card-text small text-muted mb-2">{{ Str::limit($component->description, 60) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary fw-bold">€{{ number_format($component->price, 2) }}</span>
                            
                        </div>
                        <button class="btn btn-sm btn-outline-primary w-100 mt-2 add-popular-component" 
                                data-component-id="{{ $component->id }}">
                            <i class="fas fa-plus me-1"></i>Add to Configuration
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
        <!-- Components Selection -->
        <form id="configurator-form" method="POST" action="{{ route('configurator.quote') }}">
            @csrf
            <input type="hidden" name="machine_id" value="{{ $machine->id }}">
            
            @foreach($categories as $category)
            @if($category->components->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-folder me-2"></i>{{ $category->name }}
                    </h5>
                    @if($category->description)
                    <p class="mb-0 small text-muted">{{ $category->description }}</p>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($category->components as $component)
                        <div class="col-lg-6 mb-3">
                            <div class="card component-card h-100" data-component-id="{{ $component->id }}">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input component-checkbox" 
                                               type="checkbox" 
                                               name="components[]" 
                                               value="{{ $component->id }}"
                                               id="component-{{ $component->id }}">
                                        <label class="form-check-label w-100" for="component-{{ $component->id }}">
                                            <h6 class="card-title mb-1">{{ $component->name }}</h6>
                                            <p class="card-text small text-muted mb-1">{{ $component->description }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-primary fw-bold">€{{ number_format($component->price, 2) }}</span>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $component->installation_time }}h
                                                </small>
                                            </div>
                                            <small class="text-muted">Code: {{ $component->code }}</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            @endforeach

            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customer_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="customer_company" name="customer_company">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customer_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="customer_phone" name="customer_phone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special requirements or notes..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="d-flex justify-content-between">
    <a href="{{ route('configurator.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Machines
    </a>
    <div>
        <button type="button" id="save-configuration-btn" class="btn btn-info me-2">
            <i class="fas fa-save me-2"></i>Save for Later
        </button>
        <button type="submit" id="generate-quote-btn" class="btn btn-success" disabled>
            <i class="fas fa-file-pdf me-2"></i>Generate Quote
        </button>
    </div>
</div>
        </form>
    </div>

    <!-- Price Breakdown Sidebar -->
    <div class="col-lg-4">
        <div class="card price-breakdown">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Price Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Base Machine:</span>
                        <span id="base-price">€{{ number_format($machine->base_price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Selected Components:</span>
                        <span id="components-price">€0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total Price:</span>
                        <span id="total-price">€{{ number_format($machine->base_price, 2) }}</span>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Installation Time:</span>
                        <span id="installation-time">0 hours</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Selected Components:</span>
                        <span id="components-count">0</span>
                    </div>
                </div>

                <div id="selected-components-list" class="mt-3 small">
                    <!-- Selected components will appear here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedComponents = [];

    // Component selection toggle
    $('.component-card').click(function(e) {
        if (!$(e.target).is('input')) {
            const componentId = $(this).data('component-id');
            const checkbox = $(`#component-${componentId}`);
            
            checkbox.prop('checked', !checkbox.prop('checked'));
            
            if (checkbox.prop('checked')) {
                $(this).addClass('selected');
                if (!selectedComponents.includes(componentId)) {
                    selectedComponents.push(componentId);
                }
            } else {
                $(this).removeClass('selected');
                selectedComponents = selectedComponents.filter(id => id !== componentId);
            }
            
            validateConfiguration();
        }
    });

    // Checkbox change handler
    $('.component-checkbox').change(function() {
        const componentId = $(this).val();
        const card = $(`.component-card[data-component-id="${componentId}"]`);
        
        if ($(this).prop('checked')) {
            card.addClass('selected');
            if (!selectedComponents.includes(parseInt(componentId))) {
                selectedComponents.push(parseInt(componentId));
            }
        } else {
            card.removeClass('selected');
            selectedComponents = selectedComponents.filter(id => id !== parseInt(componentId));
        }
        
        validateConfiguration();
    });

    function validateConfiguration() {
        const machineId = {{ $machine->id }};

        $.ajax({
            url: '{{ route("configurator.validate") }}',
            method: 'POST',
            data: {
                machine_id: machineId,
                components: selectedComponents,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                updatePriceBreakdown(response.pricing);
                updateCompatibilityAlerts(response.messages);
                updateGenerateButton(response.can_proceed);
                updateSelectedComponentsList(response.pricing.components);
            },
            error: function(xhr) {
                console.error('Validation failed:', xhr.responseText);
            }
        });
    }

    function updatePriceBreakdown(pricing) {
        $('#base-price').text('€' + pricing.base_price.toFixed(2));
        $('#components-price').text('€' + pricing.components_price.toFixed(2));
        $('#total-price').text('€' + pricing.total.toFixed(2));
        $('#installation-time').text(pricing.installation_time + ' hours');
        $('#components-count').text(pricing.components.length);
    }

    function updateCompatibilityAlerts(messages) {
        const alertsContainer = $('#compatibility-alerts');
        alertsContainer.empty();

        messages.forEach(function(message) {
            const alertClass = message.type === 'error' ? 'alert-danger' : 'alert-warning';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>${message.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertsContainer.append(alertHtml);
        });
    }

    function updateGenerateButton(canProceed) {
        const generateBtn = $('#generate-quote-btn');
        generateBtn.prop('disabled', !canProceed);
        
        if (canProceed) {
            generateBtn.removeClass('btn-secondary').addClass('btn-success');
            generateBtn.html('<i class="fas fa-file-pdf me-2"></i>Generate Quote');
        } else {
            generateBtn.removeClass('btn-success').addClass('btn-secondary');
            generateBtn.html('<i class="fas fa-exclamation-triangle me-2"></i>Fix Issues First');
        }
    }

    function updateSelectedComponentsList(components) {
        const listContainer = $('#selected-components-list');
        listContainer.empty();

        if (components.length > 0) {
            listContainer.append('<strong>Selected Components:</strong>');
            components.forEach(function(component) {
                listContainer.append(`<div class="mt-1">• ${component.name} (€${parseFloat(component.price).toFixed(2)})</div>`);
            });
        }
    }

    // Initial validation
    validateConfiguration();
});
</script>
@endpush
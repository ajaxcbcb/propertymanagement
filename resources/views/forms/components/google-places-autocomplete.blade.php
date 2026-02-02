<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}'),
            autocomplete: null,
            init() {
                this.initAutocomplete();
            },
            initAutocomplete() {
                const input = this.$refs.autocompleteInput;
                
                if (!window.google || !window.google.maps) {
                    console.error('Google Maps API not loaded');
                    return;
                }

                this.autocomplete = new google.maps.places.Autocomplete(input, {
                    componentRestrictions: { country: 'my' },
                    fields: ['address_components', 'formatted_address', 'geometry', 'name'],
                    types: ['address']
                });

                this.autocomplete.addListener('place_changed', () => {
                    const place = this.autocomplete.getPlace();
                    
                    if (!place.geometry) {
                        return;
                    }

                    // Set the full address
                    this.state = place.formatted_address;

                    // Extract address components
                    let city = '';
                    let state = '';
                    let postalCode = '';
                    let country = '';

                    place.address_components.forEach(component => {
                        const types = component.types;
                        
                        if (types.includes('locality')) {
                            city = component.long_name;
                        }
                        if (types.includes('administrative_area_level_1')) {
                            state = component.long_name;
                        }
                        if (types.includes('postal_code')) {
                            postalCode = component.long_name;
                        }
                        if (types.includes('country')) {
                            country = component.long_name;
                        }
                    });

                    // Update related fields
                    @if($latitudeField = $getLatitudeField())
                        $wire.set('{{ $latitudeField }}', place.geometry.location.lat());
                    @endif

                    @if($longitudeField = $getLongitudeField())
                        $wire.set('{{ $longitudeField }}', place.geometry.location.lng());
                    @endif

                    @if($cityField = $getCityField())
                        $wire.set('{{ $cityField }}', city);
                    @endif

                    @if($stateField = $getStateField())
                        $wire.set('{{ $stateField }}', state);
                    @endif

                    @if($postalCodeField = $getPostalCodeField())
                        $wire.set('{{ $postalCodeField }}', postalCode);
                    @endif

                    @if($countryField = $getCountryField())
                        $wire.set('{{ $countryField }}', country);
                    @endif
                });
            }
        }"
        wire:ignore
    >
        <input
            x-ref="autocompleteInput"
            type="text"
            x-model="state"
            {!! $isDisabled() ? 'disabled' : '' !!}
            {!! $getExtraAttributeBag()->class([
                'block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70',
                'border-gray-300' => !$errors->has($getStatePath()),
                'border-danger-600 ring-danger-600' => $errors->has($getStatePath()),
            ]) !!}
            placeholder="{{ $getPlaceholder() }}"
        />
    </div>
</x-dynamic-component>

@once
    @push('scripts')
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&callback=Function.prototype"></script>
    @endpush
@endonce

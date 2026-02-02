<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}'),
            suggestions: [],
            showSuggestions: false,
            selectedIndex: -1,
            isLoading: false,
            debounceTimer: null,
            
            async searchAddress(query) {
                if (!query || query.length < 3) {
                    this.suggestions = [];
                    this.showSuggestions = false;
                    return;
                }
                
                this.isLoading = true;
                
                try {
                    // Using Nominatim API (OpenStreetMap) - Free, no API key required
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/search?` + new URLSearchParams({
                            q: query,
                            format: 'json',
                            addressdetails: 1,
                            countrycodes: 'my', // Restrict to Malaysia
                            limit: 5
                        }),
                        {
                            headers: {
                                'User-Agent': 'PropertyManagementSystem/1.0'
                            }
                        }
                    );
                    
                    const data = await response.json();
                    this.suggestions = data;
                    this.showSuggestions = data.length > 0;
                    this.selectedIndex = -1;
                } catch (error) {
                    console.error('Error fetching addresses:', error);
                    this.suggestions = [];
                    this.showSuggestions = false;
                } finally {
                    this.isLoading = false;
                }
            },
            
            selectAddress(place) {
                // Set the full address
                this.state = place.display_name;
                
                // Extract address components
                const addr = place.address || {};
                
                const city = addr.city || addr.town || addr.village || addr.municipality || '';
                const state = addr.state || '';
                const postalCode = addr.postcode || '';
                const country = addr.country || 'Malaysia';
                const latitude = place.lat || '';
                const longitude = place.lon || '';
                
                // Update related fields
                @if($latitudeField = $getLatitudeField())
                    $wire.set('{{ $latitudeField }}', latitude);
                @endif

                @if($longitudeField = $getLongitudeField())
                    $wire.set('{{ $longitudeField }}', longitude);
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
                
                // Hide suggestions
                this.showSuggestions = false;
                this.suggestions = [];
            },
            
            handleInput() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.searchAddress(this.state);
                }, 500); // Wait 500ms after user stops typing
            },
            
            handleKeydown(event) {
                if (!this.showSuggestions) return;
                
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                } else if (event.key === 'Enter' && this.selectedIndex >= 0) {
                    event.preventDefault();
                    this.selectAddress(this.suggestions[this.selectedIndex]);
                } else if (event.key === 'Escape') {
                    this.showSuggestions = false;
                }
            }
        }"
        @click.away="showSuggestions = false"
        class="relative"
    >
        <div class="relative">
            <input
                type="text"
                x-model="state"
                @input="handleInput"
                @keydown="handleKeydown"
                {!! $isDisabled() ? 'disabled' : '' !!}
                {!! $getExtraAttributeBag()->class([
                    'block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70',
                    'border-gray-300' => !$errors->has($getStatePath()),
                    'border-danger-600 ring-danger-600' => $errors->has($getStatePath()),
                ]) !!}
                placeholder="{{ $getPlaceholder() }}"
                autocomplete="off"
            />
            
            <!-- Loading Spinner -->
            <div x-show="isLoading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        
        <!-- Suggestions Dropdown -->
        <div
            x-show="showSuggestions"
            x-transition
            class="absolute z-50 w-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 max-h-60 overflow-auto"
        >
            <template x-for="(suggestion, index) in suggestions" :key="suggestion.place_id">
                <div
                    @click="selectAddress(suggestion)"
                    :class="{
                        'bg-primary-50': index === selectedIndex,
                        'hover:bg-gray-50': index !== selectedIndex
                    }"
                    class="px-4 py-3 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors"
                >
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="suggestion.display_name"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="suggestion.type"></p>
                        </div>
                    </div>
                </div>
            </template>
            
            <template x-if="suggestions.length === 0 && !isLoading">
                <div class="px-4 py-3 text-sm text-gray-500 text-center">
                    No addresses found. Try a different search term.
                </div>
            </template>
        </div>
        
        <!-- Helper Text -->
        <p class="mt-1 text-xs text-gray-500">
            🆓 Free address search powered by OpenStreetMap. Type at least 3 characters.
        </p>
    </div>
</x-dynamic-component>

<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;

class GooglePlacesAutocomplete extends TextInput
{
    protected string $view = 'forms.components.google-places-autocomplete';

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (GooglePlacesAutocomplete $component, $state) {
            // Load the full address into the field
            $component->state($state);
        });

        $this->reactive();
    }

    public function getLatitudeField(): ?string
    {
        return $this->evaluate($this->latitudeField ?? null);
    }

    public function getLongitudeField(): ?string
    {
        return $this->evaluate($this->longitudeField ?? null);
    }

    public function getCityField(): ?string
    {
        return $this->evaluate($this->cityField ?? null);
    }

    public function getStateField(): ?string
    {
        return $this->evaluate($this->stateField ?? null);
    }

    public function getPostalCodeField(): ?string
    {
        return $this->evaluate($this->postalCodeField ?? null);
    }

    public function getCountryField(): ?string
    {
        return $this->evaluate($this->countryField ?? null);
    }

    protected ?string $latitudeField = null;
    protected ?string $longitudeField = null;
    protected ?string $cityField = null;
    protected ?string $stateField = null;
    protected ?string $postalCodeField = null;
    protected ?string $countryField = null;

    public function latitudeField(string $field): static
    {
        $this->latitudeField = $field;
        return $this;
    }

    public function longitudeField(string $field): static
    {
        $this->longitudeField = $field;
        return $this;
    }

    public function cityField(string $field): static
    {
        $this->cityField = $field;
        return $this;
    }

    public function stateField(string $field): static
    {
        $this->stateField = $field;
        return $this;
    }

    public function postalCodeField(string $field): static
    {
        $this->postalCodeField = $field;
        return $this;
    }

    public function countryField(string $field): static
    {
        $this->countryField = $field;
        return $this;
    }
}

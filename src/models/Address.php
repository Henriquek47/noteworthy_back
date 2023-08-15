<?php

namespace App\models;

class Address {
    public string $street;
    public string $city;
    public string $state;
    public string $neighborhood;
    public string $number;
    public string $complement;

    public function __construct(string $street, string $city, string $state, string $neighborhood, string $number, string $complement) {
        $this->street = $street;
        $this->city = $city;
        $this->state = $state;
        $this->neighborhood = $neighborhood;
        $this->number = $number;
        $this->complement = $complement;
    }

    public function toArray(): array {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'neighborhood' => $this->neighborhood,
            'number' => $this->number,
            'complement' => $this->complement,
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['street'],
            $data['city'],
            $data['state'],
            $data['neighborhood'],
            $data['number'],
            $data['complement']
        );
    }

    // Se necessário, você pode adicionar métodos de validação aqui
}

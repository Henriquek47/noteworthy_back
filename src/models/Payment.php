<?php

namespace App\Models;

class Payment {

    public ?string $id;
    public string $fromUserId;
    public string $toUserId;
    public float $amount;
    public \DateTime $date;
    public string $method;

    public function __construct(
        ?string $id,
        string $fromUserId,
        string $toUserId,
        float $amount,
        string $method
    ) {
        $this->id = $id;
        $this->fromUserId = $fromUserId;
        $this->toUserId = $toUserId;
        $this->amount = $amount;
        $this->date = new \DateTime(); // Define a data como agora por padrão
        $this->method = $method;
    }
}

<?php

declare(strict_types=1);

namespace Skrepr\DtoTester\Example;

class User
{
    public string $name;
    private string $email;
    public ?int $numberOfLogins = null;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}

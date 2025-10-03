<?php

namespace App\Support\Activitylog;

class LogOptions
{
    public string $logName = 'default';

    /**
     * @var array<int, string>
     */
    public array $attributes = [];

    public bool $logOnlyDirty = false;

    public bool $submitEmptyLogs = false;

    public static function defaults(): self
    {
        return new self();
    }

    public function useLogName(string $logName): self
    {
        $this->logName = $logName;

        return $this;
    }

    /**
     * @param  array<int, string>  $attributes
     */
    public function logOnly(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function logOnlyDirty(): self
    {
        $this->logOnlyDirty = true;

        return $this;
    }

    public function dontSubmitEmptyLogs(): self
    {
        $this->submitEmptyLogs = false;

        return $this;
    }

    public function submitEmptyLogs(): self
    {
        $this->submitEmptyLogs = true;

        return $this;
    }
}

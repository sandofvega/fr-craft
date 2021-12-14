<?php

namespace Fortrabbit\CraftPluginList\Models;

use DateTime;
use JsonSerializable;

class CraftPluginPackage implements JsonSerializable
{

    public function __construct(
        public string $name,
        public ?string $description,
        public string $handle,
        public string $repository,
        public ?string $testLibrary,
        public string $version,
        public int $downloads,
        public int $dependents,
        public int $favers,
        public DateTime $updated
    ) {}

    public function jsonSerialize()
    {
        return [
            'name'  => $this->name,
            'description'  => $this->description,
            'handle'  => $this->handle,
            'repository'  => $this->repository,
            'testLibrary'  => $this->testLibrary,
            'version'  => $this->version,
            'downloads'  => $this->downloads,
            'dependents'  => $this->dependents,
            'favers'  => $this->favers,
            'updated'  => $this->updated->format('Y-m-d H:i:s')
        ];
    }
}

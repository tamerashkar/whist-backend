<?php

namespace App;

use Illuminate\Database\Schema\Blueprint;

abstract class Enum
{
    const DEFAULT_VALUE = false;
    const VALUE = null;
    const VALUES = [];

    public static function create(Blueprint $table, string $column)
    {
        return $table
            ->tinyInteger($column)
            ->unsigned()
            ->default(static::DEFAULT_VALUE !== false ? static::defaultValue()->id : null);
    }

    public static function all()
    {
        return collect(static::VALUES)
            ->map(function ($name, $id) {
                return (object) ['id' => $id, 'name' => $name];
            })
            ->values();
    }

    public static function find($id)
    {
        return $id !== null ? static::all()->where('id', $id)->first() : null;
    }

    public static function id($name)
    {
        $enum = static::whereName($name);
        return $enum ? $enum->id : null;
    }

    public static function whereName($name)
    {
        return static::all()->where('name', $name)->first();
    }

    public static function defaultValue()
    {
        return static::find(static::DEFAULT_VALUE);
    }
}

<?php

namespace Sackrin\Meta\Field;

class Blueprint {

    public $blueprints;

    public $index;

    public function __construct() {
        // Where to store the blueprints
        $this->blueprints = collect([]);
        // Where to store the latest index
        $this->index = collect([]);
    }

    public function addBlueprint($field) {
        // Add to the blueprint collection
        $this->getBlueprints()->push($field);
        // Rebuild the index after each blueprint is added
        // This may seem costly but ensures index is always up to date
        $this->reIndex();
        // Return for chaining
        return $this;
    }

    public function getBlueprints() {
        // Return the blue print collection
        return $this->blueprints;
    }

    public function getBlueprint($path) {
        // Retrieve the blueprint index
        $blueprints = $this->toObjects();
        // If the blueprint does not exist then return null
        // As blueprints may change over time we don't throw an exception
        if (!isset($blueprints[$path])) { return null; }
        // Return the found blueprint object
        return $blueprints[$path];
    }


    public function getIndex() {
        // Retrieve the index collection
        return $this->index;
    }

    public function reIndex() {
        // Reset the index collection
        $index = collect([]);
        // Loop through each of the blueprints
        $this->getBlueprints()->each(function($blueprint,$k) use ($index) {
            // Pass each blueprint through the to reference process
            $blueprint->toReference($index);
        });
        // Replace the current index value
        $this->index = $index;
        // Return for chaining
        return $this;
    }


    public function toObjects() {
        // Retrieve the current blueprint index
        // Since the index is stored as reference => object we don't need to do anything to it
        return $this->getIndex();
    }

    public function toClasses() {
        // Retrieve the current blueprint index
        $index = $this->getIndex();
        // Return a collection with the reference => classname pairing
        return $index->mapWithKeys(function(Field $blueprint, $key){
            // Determine the classname and the reference route
            return [$key => get_class($blueprint)];
        });
    }

    public function toOptions() {
        // Retrieve the current field index
        $index = $this->getIndex();
        // Create a new collection with the path and classname
        return $index->mapWithKeys(function(Field $blueprint, $key){
            // Return the path with the classname
            return [$key => $blueprint->getOptions()];
        });
    }

    public function toValues() {
        // Retrieve the current field index
        $index = $this->getIndex();
        // Create a new collection with the path and classname
        return $index->mapWithKeys(function(Field $blueprint, $key){
            // Return the path with the classname
            return [$key => $blueprint->getValue()];
        });
    }

    public function toDefaults() {
        // Retrieve the current field index
        $index = $this->getIndex();
        // Create a new collection with the path and classname
        return $index->mapWithKeys(function(Field $field, $key){
            // Return the path with the classname
            return [$key => $field->getDefault()];
        });
    }

}
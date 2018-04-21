<?php

namespace ZachFlower\EloquentInteractions;

use Illuminate\Support\Facades\Validator;
use ZachFlower\EloquentInteractions\Exceptions\ValidationException;

abstract class Interaction {

  /**
   * Validation errors
   *
   * @var object
   */
  public $errors;

  /**
   * Interaction parameters
   *
   * @var array
   */
  public $params = [];

  /**
   * Whether or not validation failed
   *
   * @var boolean
   */
  public $valid;

  /**
   * The Laravel validator
   *
   * @var object
   */
  public $validator;

  /**
   * Validations
   *
   * @var array
   */
  public $validations = [];

  /**
   * Setup interaction
   *
   * @param array $params Interaction parameters
   */
  public function __construct($params = []) {
    $this->params = $params;

    $validations = method_exists($this, 'validations') ? $this->validations() : $this->validations;
    $this->validator = Validator::make($params, $validations, ['object' => 'The :attribute object type is invalid.']);
  }

  /**
   * Make interaction parameters accessible as object values
   *
   * @param  string $key
   * @return mixed
   */
  public function __get($key) {
    if ( !empty($this->params) && array_key_exists($key, $this->params) ) {
      return $this->params[$key];
    }

    return NULL;
  }

  /**
   * Check if an interaction parameter has been set
   *
   * @param  string  $key
   * @return boolean
   */
  public function __isset($key) {
    return isset($this->params[$key]);
  }

  /**
   * Static method to run the interaction as a singleton
   *
   * @param  array $params            Interaction parameters
   * @param  bool  $dangerous         Throw exceptions?
   * @return App\Interactions\Outcome Outcome of the run interaction
   */
  public static function run($params = [], $dangerous = FALSE) {
    $interactor = new static($params);
    $outcome = new Outcome($interactor);

    // run the interaction "dangerously" (return the result or fail hard)
    if ( $dangerous ) {
      if ( $outcome->valid ) {
        return $outcome->result;
      } else {
        // only throw a validation exception
        throw new ValidationException($outcome->validator);
      }
    } else {
      return $outcome;
    }
  }
}

class Outcome {

  /**
   * Whether or not the interaction executed successfully
   *
   * @var boolean
   */
  public $valid;

  /**
   * Validation errors, if any
   *
   * @var object
   */
  public $errors;

  /**
   * Original parameters
   *
   * @var object
   */
  public $params;

  /**
   * Interaction result
   *
   * @var object
   */
  public $result;

  /**
   * The Laravel validator object
   *
   * @var object
   */
  public $validator;

  /**
   * Setup outcome
   *
   * @param App\Interactions\Interaction $interactor Instantiated interactor
   */
  public function __construct(Interaction $interactor) {
    $this->validator = $interactor->validator;
    $this->params = $interactor->params;

    // only execute the interactor if it passes initial validation
    if ( !$this->validator->fails() ) {
      $this->result = $interactor->execute();

      // if the interactor adds its own error messages, then mark the result
      // as not valid and empty out the result
      if ( !($this->valid = $this->validator->errors()->isEmpty()) ) {
        $this->result = NULL;
      }
    } else {
      $this->valid = FALSE;
    }

    $this->errors = $this->validator->errors();
  }
}

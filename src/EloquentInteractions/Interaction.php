<?php namespace EloquentInteractions;

use Illuminate\Validation\Validator;
use EloquentInteractions\Exceptions\ValidationException;

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
  public $params;

  /**
   * Whether or not validation failed
   *
   * @var boolean
   */
  public $valid;

  /**
   * Actual interaction code required for interactions to work
   */
  abstract public function execute();

  /**
   * Setup interaction
   *
   * @param array $params Interaction parameters
   */
  public function __construct($params = []) {
    $validator = Validator::make($params, $this->validations);

    $this->valid = !$validator->fails();
    $this->errors = $validator->errors();
    $this->params = $params;
  }

  /**
   * Make interaction parameters accessible as object values
   *
   * @param  string $key
   *
   * @return mixed
   */
  public function __get($key) {
    if ( array_key_exists($key, $this->params) ) {
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
   * @param  bool  $dangerous         Run the interaction dangerously
   *
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
        // only throw an exception for the first error
        throw new ValidationException($outcome->errors->first());
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
   * Setup outcome
   *
   * @param App\Interactions\BaseInteraction $interactor Instantiated interactor
   */
  public function __construct(BaseInteraction $interactor) {
    $this->errors = $interactor->errors;
    $this->params = $interactor->params;

    if ( $this->valid = $interactor->valid ) {
      $this->result = $interactor->execute();
    }
  }
}

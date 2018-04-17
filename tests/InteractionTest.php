<?php

namespace ZachFlower\EloquentInteractions\Tests;

use ZachFlower\EloquentInteractions\Interaction;

class InteractionTest extends TestCase {

  public function testValidInput() {
    $outcome = ConvertMetersToMiles::run(['meters' => 10000]);

    $this->assertTrue($outcome->valid);
    $this->assertEmpty($outcome->errors);
    $this->assertEquals(6.21, round($outcome->result, 2));
  }

  public function testInvalidInput() {
    $outcome = ConvertMetersToMiles::run(['meters' => 'ten thousand']);

    $this->assertFalse($outcome->valid);
    $this->assertNull($outcome->result);
    $this->assertArraySubset(['meters' => ['The meters must be a number.']], $outcome->errors->toArray());
  }

  public function testCustomError() {
    $outcome = ConvertMetersToMiles::run(['meters' => 1]);

    $this->assertFalse($outcome->valid);
    $this->assertNull($outcome->result);
    $this->assertArraySubset(['meters' => ['Okay... but that number is a little small.']], $outcome->errors->toArray());
  }

  public function testValidObjectValidation() {
    $outcome = ConvertMetersToMiles::run(['meters' => 10000, 'inception' => new ConvertMetersToMiles()]);

    $this->assertTrue($outcome->valid);
    $this->assertEmpty($outcome->errors);
    $this->assertEquals(6.21, round($outcome->result, 2));
  }

  public function testInvalidObjectValidation() {
    $outcome = ConvertMetersToMiles::run(['meters' => 10000, 'inception' => new \StdClass()]);

    $this->assertFalse($outcome->valid);
    $this->assertNull($outcome->result);
    $this->assertArraySubset(['inception' => ['The inception object type is invalid.']], $outcome->errors->toArray());
  }

  public function testEmailDomainExample() {
    $outcome = CheckEmail::run(['email' => 'invalid email']);

    $this->assertFalse($outcome->valid);
    $this->assertNull($outcome->result);
    $this->assertArraySubset(['email' => ['The email must be a valid email address.']], $outcome->errors->toArray());
  }
}

class ConvertMetersToMiles extends Interaction {

  /**
   * Parameter validations
   *
   * @var array
   */
  public $validations = [
    'meters' => 'required|numeric|min:0',
    'inception' => 'object:ZachFlower\EloquentInteractions\Interaction'
  ];

  /**
   * Execute the interaction
   *
   * @return void
   */
  public function execute() {
    if ( $this->meters === 1 ) {
      $this->validator->errors()->add('meters', 'Okay... but that number is a little small.');
      return;
    }

    return $this->meters * 0.000621371;
  }
}

class CheckEmail extends Interaction {
  /**
   * You can use validations method to return the array of validations
   * instead of the property validations
   *
   * @return array
   */
  public function validations() {
    return [
      'email' => 'required|email',
    ];
  }

  /**
   * Execute the interaction
   *
   * @return void
   */
  public function execute() {
    // do whatever you want with the email
    return $this->email;
  }
}

<?php

class Test extends Aware 
{
  public static $timestamps = true;

  public static $rules = array(
    'url' => 'required|url',
    'type' => 'required',
  );

  public function save_options($options) 
  {
    $this->options = json_encode($options);
  }

  public function get_options() {
    return (array)json_decode($this->get_attribute('options'));
  }

  /**
   * Get some nice descriptive text about the test.
   */
  public function generate_description_for_output() {
    $description = 'Unknown Test';
    $details = array();

    switch ($this->type) {
      case 'element':
        $description = sprintf('Test for presence of element <code>%s</code>', $this->options['element']);
        if ($this->options['text']) {
          $details[] = sprintf('With text <code>%s</code>', $this->options['text']);
        }
        break;
    }

    return array('description' => $description, 'details' => $details);
  }

  /**
   * Run the test and create a TestLog.
   */
  public function run() {
    $tester = IoC::resolve('tester');
    $passed = $tester->test($this->type, $this->url, $this->options);

    $message = $passed ? 'Test Passed' : 'Test Failed'; #todo: more descriptive messages

    TestLog::create(array(
      'test_id' => $this->id,
      'message' => $message,
      'passed' => $passed,
    ));
  }

  /**
   * Destroy the record if the user is able to.
   */
  public function destroy_if_user_can($user_id) {
    if ($user_id === $this->user_id) {
      $this->delete();
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Relationships.
   */
  public function user() 
  {
    return $this->belongs_to('User');
  }

  public function logs()
  {
    return $this->has_many('TestLog');
  }
}
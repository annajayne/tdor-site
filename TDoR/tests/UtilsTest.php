<?php

    declare(strict_types=1);

    use PHPUnit\Framework\TestCase;



    final class utilsTest extends TestCase
    {
        public function test_is_valid_hex_string_leading_zero()
        {
            $this->assertEquals(true, is_valid_hex_string('03d26a47') );    // Leading zero
        }


        public function test_is_valid_hex_string_no_leading_zero()
        {
            $this->assertEquals(true, is_valid_hex_string('326a47') );      // Valid hex string
        }


        public function test_is_valid_hex_string_invalid_input()
        {
            $this->assertEquals(false, is_valid_hex_string('3qd26a47') );   // Not a valid hex string
        }


        public function test_is_valid_hex_string_empty_input()
        {
            $this->assertEquals(false, is_valid_hex_string('') );           // Not a valid hex string
        }
    }

?>
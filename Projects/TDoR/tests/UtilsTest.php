<?php

    declare(strict_types=1);

    use PHPUnit\Framework\TestCase;



    final class utilsTest extends TestCase
    {

        // str_begins_with()
        public function test_str_begins_with_not_found()
        {
            $this->assertEquals(false, str_begins_with('haystack', 'needle') );
        }


        public function test_str_begins_with_yes_it_does()
        {
            $this->assertEquals(true, str_begins_with('needleinhaystack', 'needle') );
        }


        public function test_str_begins_with_no_it_doesnt()
        {
            $this->assertEquals(false, str_begins_with('haystackwithaneedlein', 'needle') );
        }


        // str_ends_with()
        public function test_str_ends_with_not_found()
        {
            $this->assertEquals(false, str_ends_with('haystack', 'needle') );
        }


        public function test_str_ends_with_yes_it_does()
        {
            $this->assertEquals(true, str_ends_with('haystackwithneedle', 'needle') );
        }


        public function test_str_ends_with_no_it_doesnt()
        {
            $this->assertEquals(false, str_ends_with('haystackwithaneedlein', 'needle') );
        }


        // is_valid_hex_string()
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


        function test_get_link_html_basic()
        {
            $link_properties['href']    = 'http://google.com';
            $link_properties['text']    = 'Link Text';

            $expected = '<a href="http://google.com">Link Text</a>';

            $this->assertEquals($expected, get_link_html($link_properties) );
        }


        function test_get_link_html_with_onclick()
        {
            $link_properties['href']    = 'http://google.com';
            $link_properties['text']    = 'Link Text';
            $link_properties['onclick'] = 'clickme();';

            $expected = '<a onclick="clickme();" href="http://google.com">Link Text</a>';

            $this->assertEquals($expected, get_link_html($link_properties) );
        }


        function test_get_link_html_with_nofollow()
        {
            $link_properties['href']    = 'http://google.com';
            $link_properties['text']    = 'Link Text';
            $link_properties['rel']     = 'nofollow';

            $expected = '<a rel="nofollow" href="http://google.com">Link Text</a>';

            $this->assertEquals($expected, get_link_html($link_properties) );
        }


        function test_linkify()
        {
            $desc = 'some text (http://google.com/subdomain/) more text';
            $actual = linkify($desc, array('http', 'mail'), array('target' => '_blank') );

            $expected = 'some text (<a  target="_blank" href="http://google.com/subdomain/">http://google.com/subdomain/</a>) more text';

            $this->assertEquals($expected, $actual);
        }


    }

?>
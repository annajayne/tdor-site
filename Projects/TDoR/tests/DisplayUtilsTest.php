<?php

  declare(strict_types=1);

    use PHPUnit\Framework\TestCase;



    final class displayutilsTest extends TestCase
    {

        public function test_get_short_description_first_line()
        {
            $report = new Report;
            
            $report->description = "Marilyn was shot 3 times by a man on a motorcycle. She died at the scene.\n\nMarilyn was from Neiva (Huila) and had given an interview to La Patria de Manizales during Pride celebrations just a few months earlier.";

            $expected = "Marilyn was shot 3 times by a man on a motorcycle. She died at the scene.";

            $this->assertEquals($expected, get_short_description($report) );
        }


        public function test_get_short_description_single_line_truncated_text()
        {
            $report = new Report;
            
            $report->description = "Marilyn was shot 3 times by a man on a motorcycle. She died at the scene. Marilyn was from Neiva (Huila) and had given an interview to La Patria de Manizales during Pride celebrations just a few months earlier.";

            $expected = "Marilyn was shot 3 times by a man on a motorcycle. She died at the scene. Marilyn was from Neiva (Huila) and had given an interview to La Patria de Manizales during Pride...";

            $this->assertEquals($expected, get_short_description($report) );
        }


    }

?>
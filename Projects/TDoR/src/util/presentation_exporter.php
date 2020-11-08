<?php
    /**
     * Class to export reports as a Powerpoint or Open Office presentation.
     *
     */


    require_once 'lib/phppresentation/PhpPresentation/Autoloader.php';
    \PhpOffice\PhpPresentation\Autoloader::register();

    require_once 'lib/phppresentation/Common/Autoloader.php';
    \PhpOffice\Common\Autoloader::register();

    use PhpOffice\PhpPresentation\Autoloader;
    use PhpOffice\PhpPresentation\Settings;
    use PhpOffice\PhpPresentation\IOFactory;
    use PhpOffice\PhpPresentation\Slide;
    use PhpOffice\PhpPresentation\PhpPresentation;
    use PhpOffice\PhpPresentation\AbstractShape;
    use PhpOffice\PhpPresentation\DocumentLayout;
    use PhpOffice\PhpPresentation\Shape\Drawing;
    use PhpOffice\PhpPresentation\Shape\RichText;
    use PhpOffice\PhpPresentation\Shape\RichText\BreakElement;
    use PhpOffice\PhpPresentation\Shape\RichText\TextElement;
    use PhpOffice\PhpPresentation\Slide\Background\Color as BackgroundColor;
    use PhpOffice\PhpPresentation\Slide\Transition;
    use PhpOffice\PhpPresentation\Style\Alignment;
    use PhpOffice\PhpPresentation\Style\Bullet;
    use PhpOffice\PhpPresentation\Style\Color as StyleColor;



    /**
     * Class to export reports as a Powerpoint or Open Office presentation.
     *
     */
    class PresentationExporter
    {
        /** @var PhpPresentation         Presentation generator. */
        public  $presentation;

        /** @var array|string            Layout. */
        public  $layout;

        /** @var int                     The width of a slide, in pixels. */
        public  $slide_width;

        /** @var int                     The height of a slide, in pixels. */
        public  $slide_height;

        /** @var string                  The slide background colour, as an alpha-RGB value. */
        public  $background_colour;

        /** @var string                  The slide text colour, as an alpha-RGB value. */
        public  $text_colour;

        /** @var boolean                 Whether QR codes should be shown. */
        public  $show_qrcodes;

        /** @var int                     The transition time for the title slide. */
        public  $title_slide_transition_time;

        /** @var int                     The transition time for report slides. */
        public  $report_slide_transition_time;

        /** @var int                     The transition time for the closing slide. */
        public  $closing_slide_transition_time;



        /**
         * Constructor
         *
         */
        public function __construct()
        {
            $this->presentation                     = null;
            $this->layout                           = DocumentLayout::LAYOUT_SCREEN_4X3;
            $this->slide_width                      = 0;
            $this->slide_height                     = 0;
            $this->background_colour                = 'FF000000';
            $this->text_colour                      = 'FFFFFFFF';
            $this->show_qrcodes                     = true;
            $this->title_slide_transition_time      = 10000;
            $this->report_slide_transition_time     = 5000;
            $this->closing_slide_transition_time    = $this->title_slide_transition_time;
        }


        /**
         * Initialise.
         *
         */
        public function initialise()
        {
            $this->presentation = new PhpPresentation();

            // Configure the layout of the presentation
            $presentation_layout    = $this->presentation->getLayout();
            
            $presentation_layout->setDocumentLayout($this->layout, true);

            $this->slide_width      = $presentation_layout->getCX(DocumentLayout::UNIT_PIXEL);
            $this->slide_height     = $presentation_layout->getCY(DocumentLayout::UNIT_PIXEL);

            $properties             = $this->presentation->getPresentationProperties();

            // Set the presentation to loop
            $properties->setLoopContinuouslyUntilEsc(true);

            $this->presentation->setPresentationProperties($properties);
        }


        /**
         * Generate slides for the given reports.
         *
         * @param array $reports        The reports to generate slides for.
         */
        function generate($reports)
        {
            $this->configure_slide_master();

            $this->configure_title_slide();

            $slide_index = 0;

            foreach ($reports as $report)
            {
                $slide = $this->add_slide($report);

                // Debug code: detect invalid slide indices which can cause errors when opening the generated file
                // ref: https://stackoverflow.com/questions/47413413/powerpoint-charts-needs-repair and https://github.com/PHPOffice/PHPPresentation/pull/572
                $slideIndex = $this->presentation->getIndex($slide);
 
                if (++$slide_index != $slideIndex)
                {
                    log_error('ERROR: Detected invalid slide index in PresentationExporter::generate(). ref:'.__FILE__.'('.__LINE__.')');
                }
            }

            $this->add_closing_slide();
        }


        /**
         * Save the presentation to the specified file.
         *
         * @param string $pathname      The pathname to write.
         */
        function save($pathname)
        {
            if (file_exists($pathname) )
            {
                unlink($pathname);
            }

            $extension = strtolower(pathinfo($pathname, PATHINFO_EXTENSION) );

            $powerpoint_writer = null;

            switch ($extension)
            {
                case 'odp':     $powerpoint_writer = IOFactory::createWriter($this->presentation, 'ODPresentation');    break;
                case 'pptx':    $powerpoint_writer = IOFactory::createWriter($this->presentation, 'PowerPoint2007');    break;
                case 'pphpt':   $powerpoint_writer = IOFactory::createWriter($this->presentation, 'Serialized');        break;

                default:                                                                                                break;
            }

            if ($powerpoint_writer != null)
            {
                $powerpoint_writer->save($pathname);

                if (file_exists($pathname) )
                {
                    return true;
                }
            }
            return false;
        }


        /**
         * Implementation method to configure the slide master.
         *
         */
        function configure_slide_master()
        {
            $arraySlideMasters = $this->presentation->getAllMasterSlides();

            $slide_master = $arraySlideMasters[0];

            $bgcolour = new BackgroundColor();

            $bgcolour->setColor(new StyleColor($this->background_colour) );

            $slide_master->setBackground($bgcolour);
        }


        /**
         * Implementation method to configure the title slide.
         *
         */
        function configure_title_slide()
        {
            // Title page
            $slide                              = $this->presentation->getActiveSlide();

            $candle_jars_photo_pathname         = '/images/tdor_candle_jars.jpg';
            $candle_jars_photo_full_pathname    = get_root_path().$candle_jars_photo_pathname;

            $shape                              = $slide->createDrawingShape();

            $photo_size = get_image_size($candle_jars_photo_pathname);

            $photo_height = ($photo_size[1] * ($this->slide_width / $photo_size[0]));

            $shape->setName('tdor_candle_jars')
                  ->setDescription('tdor_candle_jars')
                  ->setPath($candle_jars_photo_full_pathname)
                  ->setWidth($this->slide_width)
                  ->setOffsetY( ($this->slide_height - $photo_height) / 2);

            // Create a shape (text)
            $shape = $slide->createRichTextShape()
                  ->setHeight($this->slide_height)
                  ->setWidth($this->slide_width)
                  ->setOffsetX(0)
                  ->setOffsetY();

            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $textRun = $shape->createTextRun('Trans Lives Matter: Remembering Our Dead');
            $textRun->getFont()->setBold(true);
            $textRun->getFont()->setSize(36);
            $textRun->getFont()->setColor(new StyleColor($this->text_colour) );

            $shape = $slide->createRichTextShape()
                  ->setHeight($this->slide_height)
                  ->setWidth($this->slide_width)
                  ->setOffsetX(0)
                  ->setOffsetY($this->slide_height * 0.9);

            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $textRun = $shape->createTextRun('https://tdor.translivesmatter.info/');
            $textRun->getFont()->setBold(true);
            $textRun->getFont()->setSize(24);
            $textRun->getFont()->setColor(new StyleColor($this->text_colour) );

            if ($this->show_qrcodes)
            {
                $qrcode_pathname = get_root_path().'/images/qrcode.png';

                if (file_exists($qrcode_pathname) )
                {
                    $qrcode = $slide->createDrawingShape();

                    $qrcode_width = $qrcode_height = 196 /2;

                    $qrcode->setName('qrcode')
                          ->setDescription('qrcode')
                          ->setPath($qrcode_pathname)
                          ->setHeight($qrcode_height)
                          ->setWidth($qrcode_width)
                          ->setOffsetX($this->slide_width - ($qrcode_width * 1.15) )
                          ->setOffsetY($this->slide_height - ($qrcode_height * 1.15) );

                    $qrcode->getShadow()->setVisible(true)
                                       ->setDirection(45)
                                       ->setDistance(10);
                }
            }

            $this->set_slide_transition($slide, $this->title_slide_transition_time);
        }


        /**
         * Implementation method to apply a transition to the specified slide
         *
         * @param \PhpOffice\PhpPresentation\Slide $slide   The slide to configure.
         * @param int $time_trigger                         The time for the trigger.
         */
        function set_slide_transition($slide, $time_trigger)
        {
            $transition = new Transition();

            $transition->setManualTrigger(false);
            $transition->setTimeTrigger(true, $time_trigger);
            $transition->setTransitionType(Transition::TRANSITION_DISSOLVE);
            $transition->setSpeed(Transition::SPEED_SLOW);

            $slide->setTransition($transition);
        }


        /**
         * Implementation method to add a slide for the specified report.
         *
         * @param Report $report            The report to add a slide for.
         */
        function add_slide($report)
        {
            $host               = get_root_path();

            $name               = $report->name;
            $age                = !empty($report->age) ? "Age $report->age" : '';
            $place              = $report->has_location() ? "$report->location ($report->country)" : $report->country;
            $cause              = (stripos($report->cause, 'not reported') === false) ? $report->cause : '';
            $date               = date_str_to_display_date($report->date);

            $photo_pathname     = $host.get_photo_pathname();

            if (!empty($report->photo_filename) )
            {
                $photo_pathname = get_photo_thumbnail_path($report->photo_filename);
            }

            $photo_width        = $this->slide_width;
            $photo_height       = $photo_width / 2;

            $slide              = $this->presentation->createSlide();

            $shape = $slide->createDrawingShape();

            $shape->setName('report')
                  ->setDescription('report')
                  ->setPath($photo_pathname)
                  ->setWidth($this->slide_width);

            if ($this->show_qrcodes)
            {
                $qrcode_pathname = $host.get_qrcode_pathname($report->uid);

                if (file_exists($qrcode_pathname) )
                {
                    $qrcode = $slide->createDrawingShape();

                    $qrcode_width = $qrcode_height = 196 /2;

                    $qrcode->setName('qrcode')
                          ->setDescription('qrcode')
                          ->setPath($qrcode_pathname)
                          ->setHeight($qrcode_height)
                          ->setWidth($qrcode_width)
                          ->setOffsetX($this->slide_width - ($qrcode_width * 1.15) )
                          ->setOffsetY($photo_height - ($qrcode_height * 1.15) );

                    $qrcode->getShadow()->setVisible(true)
                                       ->setDirection(45)
                                       ->setDistance(10);
                }
            }

            $colour = new StyleColor($this->text_colour);

            // Create a shape (text)
            $shape = $slide->createRichTextShape()
                  ->setHeight($this->slide_height - $photo_height)
                  ->setWidth($this->slide_width)
                  ->setOffsetX(0)
                  ->setOffsetY($photo_height);

            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $textRun = $shape->createTextRun($name);
            $textRun->getFont()->setBold(true);
            $textRun->getFont()->setSize(36);
            $textRun->getFont()->setColor($colour);

            $shape->createBreak();

            if (!empty($age) )
            {
                $textRun = $shape->createTextRun($age);
                $textRun->getFont()->setBold(false);
                $textRun->getFont()->setItalic(false);
                $textRun->getFont()->setSize(24);
                $textRun->getFont()->setColor(new StyleColor($this->text_colour) );
            }

            $shape->createBreak();

            $textRun = $shape->createTextRun($place);
            $textRun->getFont()->setBold(true);
            $textRun->getFont()->setItalic(false);
            $textRun->getFont()->setSize(30);
            $textRun->getFont()->setColor($colour);

            $shape->createBreak();

            $textRun = $shape->createTextRun(!empty($cause) ? "$cause. $date" : $date);
            $textRun->getFont()->setBold(false);
            $textRun->getFont()->setItalic(true);
            $textRun->getFont()->setSize(24);
            $textRun->getFont()->setColor($colour);

            $this->set_slide_transition($slide, $this->report_slide_transition_time);

            return $slide;
        }


        /**
         * Implementation method to add a closing slide.
         *
         */
        function add_closing_slide()
        {
            $slide = $this->presentation->createSlide();

            $colour = new StyleColor($this->text_colour);

            // Create a shape (text)
            $shape = $slide->createRichTextShape()
                  ->setHeight($this->slide_height)
                  ->setWidth($this->slide_width)
                  ->setOffsetX(0)
                  ->setOffsetY($this->slide_height / 3);

            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $textRun = $shape->createTextRun("Trans Lives Matter\n");
            $textRun->getFont()->setBold(true);
            $textRun->getFont()->setSize(36);
            $textRun->getFont()->setColor($colour);

            $shape->createBreak();

            $textRun = $shape->createTextRun('SAY THEIR NAMES');
            $textRun->getFont()->setBold(true);
            $textRun->getFont()->setSize(48);
            $textRun->getFont()->setColor($colour);

            $this->set_slide_transition($slide, $this->closing_slide_transition_time);
        }


    }

?>
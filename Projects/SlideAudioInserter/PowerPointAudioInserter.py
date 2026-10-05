import re
import win32com.client as win32

from pathlib import Path

from Mp3Indexer import Mp3Indexer


def get_spaces(num_spaces):
    # Generate a space string with the specified number of spaces
    return ' ' * num_spaces


class PowerPointAudioInserter:
    def __init__(self, audio_folder):
        # Index the MP3 files
        if audio_folder:
            self.audio_indexer = Mp3Indexer(audio_folder)

        # Leading indents for console messages
        self.indent = 2
        self.slide_indent = self.indent
        self.slide_details_indent = self.slide_indent + self.indent
        self.shape_indent = self.slide_details_indent
        self.shape_details_indent = self.shape_indent + self.indent


    def __del__(self):
        self.pp.Quit()


    def open(self, pptx_path):
        self.pptx_path = pptx_path

        self.pp = win32.Dispatch("PowerPoint.Application")

        self.presentation = self.pp.Presentations.Open(Path(pptx_path).resolve())

        print(f"Opened: {pptx_path}")

        self.width_pts  = self.presentation.PageSetup.SlideWidth
        self.height_pts = self.presentation.PageSetup.SlideHeight

        for slide in self.presentation.Slides:
            self._process_slide(slide)


    def saveas(self, output_path):
        print(f"Saving {output_path}")

        self.presentation.SaveAs(Path(output_path).resolve())


    def close(self):
        print("Closing presentation")

        self.presentation.Close()


    def _process_slide(self, slide):
        print(get_spaces(self.slide_indent) + f"Slide index: {slide.SlideIndex}")

        if self._has_audio(slide):
            return

        for shape in slide.Shapes:
            self._process_shape(shape)


    def _process_shape(self, shape):
        if shape.Name:
            print(get_spaces(self.shape_indent) + f"Shape name: {shape.Name}")
            uid = self._get_shape_uid(shape)

            if uid:
                print(get_spaces(self.shape_details_indent) + f"Report UID identified as {uid}")

                if not self._has_audio(shape.Parent):
                    audio_file = self.audio_indexer.get_file_by_uid(uid)
                    if not audio_file:
                        print(get_spaces(self.shape_details_indent) + f"No matching audio file found")
                        return

                    print(get_spaces(self.shape_details_indent) + f"Found matching audio file: {Path(audio_file).name}")

                    self._add_audio(shape, audio_file)


    def _get_shape_uid(self, shape):
        uid = ""
        if not shape.Name:
            return ''

        if not shape.Name.startswith('Report_'):
            return ''

        pos = shape.Name.rfind('_')
        if pos < 0:
            return ''

        uid = shape.Name[pos + 1:]

        return uid


    def _has_audio(self, slide):
        for shape in slide.Shapes:
            if shape.Name.startswith("Audio_"):
                print(get_spaces(self.slide_details_indent) + f"Slide has audio embedded in {shape.Name}")
                return True

        print(get_spaces(self.slide_details_indent) + "Slide has no embedded audio")

        return False


    def _add_audio(self, shape, audio_file):
        slide = shape.Parent

        # PowerPoint constants
        msoFalse = 0
        msoTrue = -1
        msoAnimEffectMediaPlay = 83 # ref: https://learn.microsoft.com/en-us/office/vba/api/powerpoint.msoanimeffect
        msoAnimTriggerAfterPrevious = 3 # ref: https://learn.microsoft.com/en-us/office/vba/api/powerpoint.msoanimtriggertype

        # Animation trigger types
        ppAfterPrevious = 3

        icon_size = 50

        timeline = slide.TimeLine
        main_seq = timeline.MainSequence

        last_effect = None
        for effect in main_seq:
            last_effect = effect  # last in sequence wins

        # Insert the audio shape. This is located off-canvas at the bottom right of the slide
        audio_shape = slide.Shapes.AddMediaObject2(
            audio_file,
            msoFalse,   # link?
            msoTrue,    # save with document
            self.width_pts + icon_size, self.height_pts - icon_size,   # bottom right - should be off-slide to the right
            icon_size, icon_size
        )

        audio_shape.Name = "Audio_" + audio_shape.Name

        # Add an animation to allow the audio to playback automatically
        audio_effect = main_seq.AddEffect(
            audio_shape,
            effectId = msoAnimEffectMediaPlay,
            trigger = msoAnimTriggerAfterPrevious
        )

        # If the slide has animations, chain audio after the final effect
        if last_effect:
            audio_effect.Timing.TriggerType = ppAfterPrevious
            audio_effect.Timing.TriggerDelayTime = 0
        else:
            # No animations: simply auto-play after slide loads
            audio_effect.Timing.TriggerType = ppAfterPrevious

        print(get_spaces(self.shape_details_indent) + f"Added shape {audio_shape.Name} with autoplaying audio {Path(audio_file).name}")

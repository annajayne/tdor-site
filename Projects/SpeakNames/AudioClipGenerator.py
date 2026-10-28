import os
import pyttsx3
import random
import sys

sys.path.append('../')

from datetime import datetime
from itertools import chain
from pathlib import Path
from pydub import AudioSegment
from unidecode import unidecode

from CountryCodes import get_language_from_country
from ParseDateStr import str_to_datetime
from NativeCsvFileReader import NativeCsvFileReader
from TTSEngine import TTSEngine


class AudioClipGenerator:
    def __init__(self, tts):
        self._tts = tts


    def create_audio_clip(self, name, pathname, matching_voices = []):
        if len(matching_voices) > 0:
            if len(matching_voices) >= 2:
                random.seed()
                voice_index = random.randrange(0, len(matching_voices) - 1)
            else:
                voice_index = 0

            voice_info  = matching_voices[voice_index]

        voice_name = voice_info.get_name()
        voice_language = voice_info.get_language()

        print(f"    Chosen voice: {voice_name} [{voice_language}]")
        print(f"    Writing {pathname}")

        self._tts.save_to_file(voice_info, self._sanitise_name(name), pathname)

        return voice_name, voice_language


    def _sanitise_name(self, name):
        name = name.replace('\u00a8', '"')

        nickname_pos = name.find('("')
        if nickname_pos >= 0:
            name = name[:nickname_pos]
            name = name.strip()

        # Strip off alternate name
        slash_pos = name.find('/')
        if slash_pos >= 0:
            name = name[:slash_pos]
            name = name.strip()

        return name


    # Determine an appropriate filename (NB: this must be ASCII) for the given report
    def get_file_basename(self, report):
        name = report.get_name()
        country = report.get_country()

        language_and_region = get_language_from_country(country)

        name = self._sanitise_name(name)

        # Remove any leftover quotes
        name = name.replace("'", '')
        name = name.replace('"', '')

        # https://stackoverflow.com/questions/3194516/replace-special-characters-with-ascii-equivalent
        ascii_name = unidecode(name)

        dt = str_to_datetime(report.get_date())
        date = datetime.strftime(dt, '%Y_%m_%d')

        # Determine an appropriate filename (NB: this must be ASCII) and select appropriate voices
        ascii_name = ascii_name.replace(' ', '-')
        basename = date + '_' + ascii_name + '_' + report.get_uid()

        return basename


    def generate(self, input_dir_or_file, output_dir_or_file, voices, force):
        if not output_dir_or_file:
            output_dir_or_file = os.path.dirname(input_dir_or_file)

        csv_files = []

        if input_dir_or_file.find('.csv') > 0:
            # Single file
            csv_files.append(input_dir_or_file)
        else:
            # All matching files in the directory
            for path in Path(input_dir_or_file).glob("*.csv"):
                csv_files.append(path)

        for csv_file in csv_files:
            reader = NativeCsvFileReader()
            reports = reader.read(csv_file);

            for report in reports:
                #   1. Parse the name, stripping off nicknames or alternative (delimited by "/")
                #   2. Convert the date to ISO format
                #   3. Generate an appropriate filename (NB: must be ASCII)
                #   4. Choose appropriate voices
                #   5. Create the MP3

                # Choose a filename and select appropriate voices
                name = report.get_name()

                # TODO(SpeakNames): If the output dir does not exist, bail out or create it
                file_basename = os.path.join(output_dir_or_file, self.get_file_basename(report))

                pathname_mp3 = file_basename + ".mp3"
                pathname_log = file_basename + ".log"

                if os.path.exists(pathname_mp3) and not force:
                    # Target already exists - skip unless --force is specified
                    continue

                country = report.get_country()
                language_and_region = get_language_from_country(country)

                matching_voices = voices.get_voices(language_and_region)
                matching_voices_count = len(matching_voices)

                # If no matching voices were found, pick one at random
                if (matching_voices_count == 0):
                    matching_voices = list(chain.from_iterable(voices.get_all_voices().values()))

                dt = str_to_datetime(report.get_date())
                date = datetime.strftime(dt, '%Y_%m_%d')

                print(f"{name} - {date}: {country}")
                print(f"    {matching_voices_count} matching {language_and_region} voices found")

                # Create the audio
                if os.path.exists(pathname_mp3):
                    os.remove(pathname_mp3)

                voice_name, voice_language = self.create_audio_clip(name, pathname_mp3, matching_voices)

                # Create and write logfile
                if os.path.exists(pathname_log):
                    os.remove(pathname_log)

                with open(pathname_log, "w", encoding="utf-8") as f:
                    f.write(f"{name} - {date}: {country} [{language_and_region}]\n")
                    f.write(f"{matching_voices_count} matching {language_and_region} voices found\n")
                    f.write(f"Chosen voice: {voice_name} [{voice_language}]\n")

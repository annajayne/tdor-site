import os
import pyttsx3
import random

from datetime import datetime
from pathlib import Path
from pydub import AudioSegment
from unidecode import unidecode

from ReadImportCsvFile import ImportCsvFileReader


class NamesToAudio:

    @staticmethod
    def get_language_from_country(country):
        languages = {}

        languages['Argentina']          = 'es-AR'
        languages['Australia']          = 'en-AU'
        languages['Bangladesh']         = 'bn-IN'
        languages['Bolivia']            = 'es-BO'
        languages['Brazil']             = 'pt-BR'
        languages['Canada']             = 'en-CA'
        languages['Chile']              = 'es-CL'
        languages['Colombia']           = 'es-CO'
        languages['Costa Rica']         = 'es-CR'
        languages['Cuba']               = 'es-CU'
        languages['Dominican Republic'] = 'es-DO'
        languages['Finland']            = 'fi-FI'
        languages['Fiji']               = 'en-FJ'
        languages['France']             = 'fr-FR'
        languages['Guatemala']          = 'es-GT'
        languages['Iran']               = 'fa-IR'
        languages['Ivory Coast']        = 'fr-CI'
        languages['Jamaica']            = 'en-JM'
        languages['Myanmar']            = 'my-MM'
        languages['Netherlands']        = 'nl-NL'
        languages['Pakistan']           = 'ur-PK'
        languages['Peru']               = 'es-PE'
        languages['Russia']             = 'ru-RU'
        languages['Spain']              = 'es-ES'
        languages['Thailand']           = 'th-TH'
        languages['Turkey']             = 'tr-TR'
        languages['Ecuador']            = 'es-EC'
        languages['Honduras']           = 'es-HN'
        languages['India']              = 'hi-IN'
        languages['Italy']              = 'it-IT'
        languages['Mexico']             = 'es-MX'
        languages['Philippines']        = 'en-PH'
        languages['United Kingdom']     = 'en-GB'
        languages['Uruguay']            = 'es-UR'
        languages['USA']                = 'en-US'
        languages['Venezuela']          = 'es-VE'

        if country in languages:
            return languages[country]

        return ''


    def GenerateAudioForName(self, name, pathname, matching_voices = []):
        voice_no = 0

        if len(matching_voices) > 0:
            if len(matching_voices) >= 2:
                random.seed()
                voice_index = random.randrange(0, len(matching_voices) - 1)
            else:
                voice_index = 0

            voice_info  = matching_voices[voice_index]
            voice_no = voice_info.get_number()

        engine = pyttsx3.init("sapi5")

        voice = engine.getProperty("voices")[voice_no] 
        engine.setProperty('voice', voice.id)

        # Drop the rate, as it always seems to be way too high (200)
        engine.setProperty('rate', 100)

        voice_name = voice.name
        voice_gender = voice.gender
        voice_languages = voice.languages

        print(f"  Chosen voice: {voice_name}; {voice_languages}; {voice_gender}")

        engine.say(name) # If we don't do this, runAndWait() can block
        engine.save_to_file(name, pathname)

        engine.runAndWait() # don't forget to use this line
        engine.stop()


    # Determine an appropriate filename (NB: this must be ASCII) for the given report
    def choose_audio_filename(self, report, extension):
        name = report.get_name()
        country = report.get_country()

        language_and_region = self.get_language_from_country(country)

        nickname_pos = name.find('("')
        if nickname_pos >= 0:
            name = name[:nickname_pos]
            name = name.strip()

        # Strip off alternate name
        slash_pos = name.find('/')
        if slash_pos >= 0:
            name = name[:slash_pos]
            name = name.strip()

        # https://stackoverflow.com/questions/3194516/replace-special-characters-with-ascii-equivalent
        ascii_name = unidecode(name)

        dt = datetime.strptime(report.get_date(), "%d-%b-%Y")
        date = datetime.strftime(dt, '%Y_%m_%d')

        # Determine an appropriate filename (NB: this must be ASCII) and select appropriate voices
        ascii_name = ascii_name.replace(' ', '-')
        filename = date + '_' + ascii_name + extension

        return filename


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
            reader = ImportCsvFileReader()
            reports = reader.read(csv_file);

            for report in reports:
                #   1. Parse the name, stripping off nicknames or alternative (delimited by "/")
                #   2. Convert the date to ISO format
                #   3. Generate an appropriate filename (NB: must be ASCII)
                #   4. Choose appropriate voices
                #   5. Create the mp3

                # Choose a filename and select appropriate voices
                name = report.get_name()

                filename_wav = self.choose_audio_filename(report, ".wav")
                pathname_wav =  os.path.join(output_dir_or_file, filename_wav)

                filename_mp3 = Path(filename_wav).with_suffix(".mp3")
                pathname_mp3 =  os.path.join(output_dir_or_file, filename_mp3)

                if os.path.exists(pathname_mp3) and not force:
                    # Target already exists - skip unless --force is specified
                    continue

                name = report.get_name()
                country = report.get_country()

                language_and_region = self.get_language_from_country(country)

                matching_voices = voices.get_voices(language_and_region)
                matching_voices_count = len(matching_voices)

                dt = datetime.strptime(report.get_date(), "%d-%b-%Y")
                date = datetime.strftime(dt, '%Y_%m_%d')

                print(f"{name} - {date}: {country}; {matching_voices_count} matching {language_and_region} voices found. FileName = {filename_mp3}")

                # Create the audio
                if os.path.exists(pathname_wav):
                    os.remove(pathname_wav)

                if os.path.exists(pathname_mp3):
                    os.remove(pathname_mp3)

                self.GenerateAudioForName(name, pathname_wav, matching_voices)

                audio = AudioSegment.from_wav(pathname_wav)
                audio.export(pathname_mp3, format="mp3", tags={'title': name, 'artist': 'pyttsx3', 'album': 'TDoR'})

                os.remove(pathname_wav)

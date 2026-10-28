# Script to allow the autogeneration of name reading audio using SAPI5 (Windows)

import argparse
import os
import sys

sys.path.append('../')

from pathlib import Path
from AudioClipGenerator import AudioClipGenerator
from TTSEngine import TTSEngine
from VoicesInfo import VoicesInfo
from VoicesConfigFileEditor import VoicesConfigFileEditor


def to_absolute_path(path):
    if not os.path.isabs(path):
        current_dir = Path.cwd()
        path = os.path.join(current_dir, path)

    return path


def validate(args):
    if args.read_voices:
        return True

    if args.load_voices and args.list_voices:
        return True

    if args.load_voices and args.input:
        return True

    if args.force and args.input:
        return True

    return False


def run():
    parser = argparse.ArgumentParser()

    parser.add_argument('-i','--input', help = 'The pathname of a CSV file (or folder containing the CSV files) to read the names from', required=False)
    parser.add_argument('-o','--output', help = 'The path of the output folder where audio files will be generated. Requires --input', required=False)

    parser.add_argument('-r','--read-voices', help = 'Read the available voices from the system. Infer --list-voices', action='store_true', required=False)
    parser.add_argument('-k','--speak-voices', help = 'Speak the voices as they are read from the system. Requires --read-voices', action='store_true', required=False)
    parser.add_argument('-v','--list-voices', help = 'List the available SAPI5 voices. Requires --read-voices or --load-voices', action='store_true', required=False)

    parser.add_argument('-l','--load-voices', help = 'Load the available voices from a config file', action='store_true', required=False)
    parser.add_argument('-s','--save-voices', help = 'Save the available voices to a config file', action='store_true', required=False)

    parser.add_argument('-f','--force', help = 'Replace any existing audio clips found (if not specified, only clips which do not already exist will be generated). Requires --input', action='store_true', required=False)

    try:
        args = parser.parse_args()
    except:
        exit(0)

    output_dir_or_file = ""

    voices = VoicesInfo()

    valid = validate(args)

    if not valid:
        return False

    tts = TTSEngine()

    if args.read_voices:
        # Read and list details of the voices available on this system
        tts.read_voices(args.speak_voices)

        voices = tts.get_voices()

    if args.load_voices:
        # TODO(SpeakNames): Parameterise pathname
        config_file = VoicesConfigFileEditor()

        voices =  config_file.load()

    if args.save_voices:
        # Save details of the given voices to a config file
        config_file = VoicesConfigFileEditor()

        config_file.save(voices)

    if args.list_voices and not args.read_voices:
        # List details of the voices available (whether read from the system or a config file)
        # Not needed if --read-voices is specified since it does something similar
        for lang, lang_voices in voices.get_all_voices().items():
            for voice in lang_voices:
                print(f"    Voice {voice.get_number()}")
                print(f"        Name: {voice.get_name()}")
                print(f"        Language: {voice.get_language()}")

    if args.output:
        output_dir_or_file = to_absolute_path(args.output)
        # TODO(SpeakNames): if a folder, check that it exists

    if args.input:
        if voices.get_all_voices() == {}:
            print('Error: no voices are available. Load them from a config file or read them from the system')
            exit(1)

        # Parse the CSV file, extract the names (filtering out nicknames and the bits after slashes) & generate MP3 files
        input_dir_or_file = to_absolute_path(args.input)

        if input_dir_or_file.find('.csv') > 0 and not os.path.exists(input_dir_or_file):
            print(f"Input file/folder {args.input} not found")
            exit(1)

        encoder = AudioClipGenerator(tts);

        encoder.generate(input_dir_or_file, output_dir_or_file, voices, args.force)

    if not valid :
        print("Error: incorrect parameters")
        exit(1)


if __name__ == "__main__":
    run()

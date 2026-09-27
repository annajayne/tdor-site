# MakeNamesAudio


**MakeNamesAudio** is a Python script which allows the automated generation of MP3 audio files voicing the names of deceased trans people we want to remember at Transgender Day of Remembrance (TDoR) vigils.
 
It uses text to speech (TTS) voices to read the names found in a comma separated values (`.csv`) file and save them to individual MP3 files for each name.

The output is a collection of individual MP3 files - one for each voice. These can be played (via a playlist or other means) at a TDoR vigil, embedded in PowerPoint slides etc. - interspersed with manual readings if appropriate.

This reduces the need to find readers for all names, which can sometimes be tricky if there are a lot of names or many are from regions unfamiliar to the reader(s).

**Note:** The current version of the script is Windows specific and uses the SAPI5 (Speech Application Programming Interface) text-to-speech engine to enumerate voices built into the Window operating system (see ``Settings -> Time & Language -> Speech`) 


## Prerequisites

To run this script you will need:

- A Windows 10/11 installation (though only the latter has been tested with suitable TTS voices)
- A [Python 3.x](https://www.python.org/downloads/) interpreter and the Python libraries the script uses (see below)
- [NaturalVoiceSAPIAdapter](https://github.com/gexgd0419/NaturalVoiceSAPIAdapter) to connect Windows natural TTS voices to SAPI5. This may not be required on Windows 10 or early Windows 11 installations but should be compatible with both.
 
The Python libraries used by the script are:

- audioop-lts
- comtypes
- pydub
- pypiwin32
- pyttsx3
- pywin32
- Unidecode
 
These library dependencies can be installed via the Python **pip** utility using the command line:

```
pip install -r requirements.txt
```

You will also need to install [`FFmpeg`](https://www.ffmpeg.org/) and add it to the `PATH`.

**Tip:** if you are using the `Chocolatey` package manager, `choco install ffmpeg` will do everything for you.


Once that has been done, I recommend installing [NaturalVoiceSAPIAdapter](https://github.com/gexgd0419/NaturalVoiceSAPIAdapter) and then following the "Seeing and hearing the available voices" section below.
 

## Command Line Arguments

- `--input` or `-i`: Specifies the pathname of a .csv file (or the path of a folder containing the `.csv` files) from which names will be read.
- `--output` or `-o`: The path of a folder to which generated MP3 files should be written. Required if `--input` is specified.
- `--force` or `-f`: Replace any existing audio clips found in the output directory. If not specified, the creation of new audio clips for names matching these files will be skipped.
- `--read-voices` or `-r`: Read details of the available voices.
- `--list-voices` or `-v`: List details of the available voices. Requires `--read-voices` or `--load-voices`.
- `--speak-voices`: Speak the voice names as they are read by `--read_voices`. This allows you to hear what each voice sounds like.
- `--load-voices`: Load voice details from a configuration (`.ini`) file.
- `--save-voices`: Save details of the available voices to a configuration (`.ini`) file.
- `--help` or `-h` : Display help on available commands.


## Usage


### Seeing and hearing the available voices

After configuring the prerequisites for the script, the best starting point is to list the voices on the system:

```
python MakeNamesAudio.py --read-voices --list-voices --speak-voices
```

If this works, you should see a scrolling list of voices, with each one being read out as it is enumerated:

**Important Note:** If the script crashes in `pyttsx3.init()` when run with ``--read-voices`, the `pyttsx3` library may need to be patched (I found this out the hard way, but it is also documented on GitHub at [https://github.com/gexgd0419/NaturalVoiceSAPIAdapter/issues/60](https://github.com/gexgd0419/NaturalVoiceSAPIAdapter/issues/60)).

To do this, locate your Python installation (e.g. `C:\Python313`) and browse to the folder `Lib\site-packages\pyttsx3\drivers` within it. Open the file `sapi5.py` in a text editor, and look for the `_toVoice()` method. Within it should be a line like:

```
language_code = int(language_attr, 16)
```
        
The problem with this is that it will throw an exception if `language_attr` contains multiple values. To work around this, add following the line immediately before it:

```
language_attr = language_attr.split(';')[0]
```

You should then be able to use `--read-voices` successfully.


### Generating audio files for a set of names

At its simplest, you can generate audio files for the names in a ``.csv` file and save them to a specified directory using:

```
python MakeNamesAudio.py --read-voices --input "/path/to/names.csv" --output "/path/to/output-folder"
```


The generated files will be named with the date (in ISO format), name of the person and - if available in the `.csv` file - the permalink is used to tag the filename with the uid ("unique identifier") of the corresponding page on the [https://tdor.translivesmatter.info](https://tdor.translivesmatter.info) website.

**Note:** The uid is added to the filename to allow automated importing of the audio files to presentation slides. However, this is not yet implemented.

e.g.: ``2024_07_03_Maria-de-los-Angeles-Diaz_34d7f282.mp3`

For each name, the country corresponding to the name is used to select an appropriate voice, which helps with pronunciation. Voices are chosen at random from those from the matching country or region.

**Tip:** A suitable `.csv` file containing names etc can be downloaded from [https://tdor.translivesmatter.info/reports](https://tdor.translivesmatter.info/reports).  Just select the options you want (dates, countries etc.) and click the "Download Data" link. This will download a zipfile which includes a ``.csv` file compatible with this script.

By default audio files are only generated if they do not exist in the `--output` folder. if you want to force the generation of new audio clips even if the corresponding MP3 files already exist, add the `--force` switch.


### Saving/Loading details of voices

Because `--read-voices` is not the fastest operation, two switches are provided to allow loading and saving from a configuration (`.ini`) file,

To save and details of the available voices to a `.ini` file, use:

```
python MakeNamesAudio.py --read-names --save-voices "/path/to/voices.ini"
```

To load details of the available voices from the `.ini` file and use them to generate MP3 files, use:

```
python MakeNamesAudio.py --load-voices "/path/to/voices.ini" --input "/path/to/names.csv" --output "/path/to/output-folder"
```


*TODO: Elaborate on ini files, e.g. allowing specific voices to be excluded (e.g. if it doesn't sound great, or if the voice is inappropriate in some way)*


## Notes for developers

The `pyttsx3` library does most of the work, but on Windows it supports only SAPI5 voices. Although recent versions of Windows 11 ship with a wide variety of "natural" TTS voices, these do not have SAPI5 interfaces and this is why [NaturalVoiceSAPIAdapter](https://github.com/gexgd0419/NaturalVoiceSAPIAdapter) is so useful.

More information on NaturalVoiceSAPIAdapter can be found in the article [How to add natural voices in Microsoft text to speech (TTS)](https://medium.com/@kevostein2k47/how-to-add-natural-voices-in-microsoft-text-to-speech-tts-67d0ee5a9973).

Although `pyttsx3` also supports [espeak](https://espeak.sourceforge.net/) voices (which are cross-platform and also work on Linux), these are probably not good enough to use in a TDoR context so the script does not use them.

The script supports a variety of languages and countries, but if you encounter issues with specific voices or language recognition, refer to the `SapiVoiceReader` class for the voice parsing logic.

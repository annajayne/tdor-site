import configparser

from VoicesInfo import VoicesInfo
from VoiceInfo import VoiceInfo


class VoicesConfigFile:
    def __init__(self):
        self.pathname = "voices.ini"


    def load(self):
        voices = VoicesInfo()

        config = configparser.ConfigParser()

        config.read(self.pathname)

        language_count = int(config['languages']['Count'])

        for lang_index in range(language_count):
            lang         = config['languages'][str(lang_index +1)]

            lang_section = 'language:' + lang
            voice_count  = int(config[lang_section]['Count'])

            for voice_index in range(voice_count):
                voice_name     = config[lang_section][str(voice_index + 1)]

                voice_section  = 'voice:' + voice_name

                voice_number   = config[voice_section]['number']
                voice_id       = config[voice_section]['id']
                voice_gender   = config[voice_section]['gender']
                voice_language = config[voice_section]['language']

                voice = VoiceInfo()

                voice.set_number(int(voice_number))
                voice.set_id(voice_id)
                voice.set_name(voice_name)
                voice.set_gender(voice_gender)
                voice.set_language(voice_language)

                voices.add_voice(voice)

        return voices


    def save(self, sapi_voices):
        config = configparser.ConfigParser()

        voices = sapi_voices.get_all_voices()

        # self.voices is keyed by language
        config['languages']          = {}
        config['languages']['Count'] = str(len(voices))

        language_index = 1

        # Languages & regions
        for lang, lang_voices in voices.items():
            config['languages'][str(language_index)] = lang

            section = "language:" + lang

            config[section]          = {}
            config[section]['Count'] = str(len(lang_voices))

            voice_index = 1

            for voice in lang_voices:
                name = voice.get_name()

                config[section][str(voice_index)] = name

                voice_index += 1

            language_index += 1

        # Voices
        for lang, lang_voices in voices.items():
            section = "language:" + lang

            config[section]['Count'] = str(len(lang_voices))

            for voice in lang_voices:
                section = 'voice:' + voice.get_name()

                config[section] = {}

                config[section]['number']   = str(voice.get_number())
                config[section]['id']       = voice.get_id()
                config[section]['gender']   = voice.get_gender()
                config[section]['language'] = voice.get_language()

        with open(self.pathname, 'w') as configfile:
            config.write(configfile)

from VoiceInfo import VoiceInfo


class VoicesInfo:
    def __init__(self):
        self.voices = {}


    def add_voice(self, voice):
        language = voice.get_language()

        if language in self.voices:
            self.voices[language].append(voice)
        else:
            self.voices[language] = [voice]


    def get_all_voices(self):
        return self.voices


    def get_voices(self, language_and_region):
        if language_and_region in self.voices:
            return self.voices[language_and_region]

        matching_voices = []

        # If there isn't a voice matching the specified language & dialect,
        # try to choose one from the same language in another dialect
        # e.g.: for es-EC, we could use es-MX, es-CO etc.
        spoken_language = language_and_region[:2]

        if len(spoken_language) > 0:
            for voice_lang_id in self.voices_info:
                #print(voice_lang_id)
                if voice_lang_id[:2] == spoken_language:
                    for voice_specific_language in self.voice[voice_lang_id]:
                        matching_voices.append(voice_specific_language)

        if len(matching_voices) == 0:
            matching_voices = self.voices['en-GB']

        # TODO fallback to a voice in the same region, then en-GB if none other were found
        return matching_voices

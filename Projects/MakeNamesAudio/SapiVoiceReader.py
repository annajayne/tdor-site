import pyttsx3

from VoiceInfo import VoiceInfo
from VoicesInfo import VoicesInfo


class SapiVoiceReader:
    def __init__(self):
        self.voices = VoicesInfo()

        engine = pyttsx3.init("sapi5")

        engine_voices = engine.getProperty("voices")

        print("Enumerating SAPI voices")

        self.voices_info = {}

        for voice_number in range(0, len(engine_voices)):
            # if (voice_no > 10):
            #     continue    # temp

            voice = engine.getProperty("voices")[voice_number] 

            print(f"    Voice {voice_number}")
            print(f"        Name: {voice.name}")
            print(f"        Gender: {voice.gender}")
            print(f"        Languages: " + ' '.join(voice.languages))

            if voice.name == 'Microsoft Hazel Desktop - English (Great Britain)':
                # Sounds terrible - skip
                print("      Voice skipped")
                continue

            info = VoiceInfo()

            info.set_number(voice_number)
            info.set_id(voice.id)
            info.set_name(voice.name)
            info.set_gender(voice.gender)
            info.set_languages(voice.languages)

            self.voices.add_voice(info)

        engine.stop()


    def get_voices(self):
        return self.voices

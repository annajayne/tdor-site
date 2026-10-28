from SapiVoiceReader import SapiVoiceReader
from VoicesInfo import VoicesInfo


class TTSEngine:
    def __init__(self, engine="sapi5"):
        self.backend = self._load_backend(engine)


    def _load_backend(self, engine):
        if engine == "sapi5":
            return SapiVoiceReader()

        raise ValueError("Unknown TTS engine")


    def read_voices(self, speak=False):
        return self.backend.read_voices(speak)


    def get_voices(self):
        return self.backend.get_voices()


    def save_to_file(self, voice, text, pathname):
        self.backend.save_to_file(voice, text, pathname)


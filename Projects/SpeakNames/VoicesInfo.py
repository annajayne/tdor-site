from VoiceInfo import VoiceInfo


class VoicesInfo:
    _voices = {}

    # Fallback country mappings
    # (used when we don't have all the voices we need)
    RELATED_COUNTRIES = {
        "BD": "IN",  # Bangladesh -> India
        "LK": "IN",  # Sri Lanka -> India
        "NP": "IN",  # Nepal -> India
        "PK": "IN",  # Pakistan -> India

        "CO": "MX",  # Colombia -> Mexico
        "EC": "MX",  # Ecuador -> Mexico
        "GT": "MX",  # Guatemala -> Mexico
        "HN": "MX",  # Honduras -> Mexico
        "NI": "MX",  # Nicaragua -> Mexico
        "PA": "MX",  # Panama -> Mexico
        "SV": "MX",  # El Salvador -> Mexico
        "UR": "MX",  # Uruguay -> Mexico
        "VE": "MX",  # Venezuela -> Mexico

        "AT": "DE",  # Austria -> Germany
        "CH": "DE",  # Switzerland -> Germany

        "NZ": "AU",  # New Zealand -> Australia

        "AU": "GB",  # Australia -> UK
        "CA": "GB",  # Canada -> UK
        "IE": "GB",  # Ireland -> UK

        "TW": "CN",  # Taiwan -> China
    }


    def add_voice(self, voice):
        language = voice.get_language()

        if language in self._voices:
            self._voices[language].append(voice)
        else:
            self._voices[language] = [voice]


    def get_all_voices(self):
        return self._voices


    # Return matching voices using the following priority:
    #
    #   1. Exact language-country match (e.g. es-SV)
    #   2. Country-only match (e.g. any *-SV)
    #   3. Language-only match (e.g. any es-*)
    #
    def get_voices(self, language_and_region):
        iso_code = language_and_region.lower()
 
        # Exact match
        exact_matches = self._voices.get(iso_code, [])
        if exact_matches:
            return exact_matches
 
        try:
            language, country = iso_code.split("-", 1)
        except ValueError:
            language = iso_code
            country = None
 
        # Country-only match
        if country:
            country_matches = []
            for code, voices in self._voices.items():
                parts = code.split("-", 1)
                if len(parts) == 2 and parts[1] == country:
                    country_matches.extend(voices)
 
            if country_matches:
                return country_matches
 
        # Language-only match
        language_matches = []
        for code, voices in self._voices.items():
            if code.split("-", 1)[0] == language:
                language_matches.extend(voices)
 
        if language_matches:
            return language_matches

        # Related-country match
        # TODO(SpeakNames): Add support for double redirects (e.g. NZ->AU->UK)
        if country:
            related_country = self.RELATED_COUNTRIES.get(country.upper())

            if related_country:
                related_matches = [
                    voice
                    for code, voices in self._voices.items()
                    if code.endswith(f"-{related_country.lower()}")
                    for voice in voices
                ]

                if related_matches:
                    return related_matches 

        return []

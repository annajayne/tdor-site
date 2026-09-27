import pyttsx3
import re

from VoiceInfo import VoiceInfo
from VoicesInfo import VoicesInfo


class SapiVoiceReader:
    def __init__(self, speak):
        self.voices = VoicesInfo()

        # Note: If init() fails, install NaturalVoiceSAPIAdapter - this will
        # also give access to a much greater selection of voices.
        #
        # ref: https://github.com/gexgd0419/NaturalVoiceSAPIAdapter
        engine = pyttsx3.init("sapi5")

        # Note: If you see an error such as "ValueError: invalid literal for int() with base 16: '436;36'" here,
        # you will need to patch sapi5.py to adjust the _toVoice() implementation as follows:
        # language_attr = language_attr.split(';')[0]
        engine_voices = engine.getProperty("voices")

        print("Enumerating SAPI voices")

        self.voices_info = {}

        for voice_number in range(0, len(engine_voices)):
            # if (voice_no > 10):
            #     continue    # temp

            voice = engine.getProperty("voices")[voice_number] 

            print(f"    Voice {voice_number}")
            print(f"        Name: {voice.name}")

            # TODO(MakeNamesAudio) investigate why --speak-voices doesn't work reliably with SAPI5 (sometimes it only speaks the first voice)
            if speak:
                engine.setProperty('voice', voice.id)
                engine.setProperty('rate', 150)
                engine.say(voice.name)
                engine.runAndWait();

            language = SapiVoiceReader.__infer_language_from_voice_name(voice.name)

            print(f"        Language: {language}")

            if voice.name == 'Microsoft Hazel Desktop - English (Great Britain)':
                # Microsoft Hazel sounds terrible - so skip it
                print("        Voice skipped - low quality")
                continue

            if not language:
                print("        Voice skipped - language or country not decoded")
                continue

            info = VoiceInfo()

            info.set_number(voice_number)
            info.set_id(voice.id)
            info.set_name(voice.name)
            info.set_language(language)
            self.voices.add_voice(info)

        engine.stop()


    def get_voices(self):
        return self.voices


    # Extracts the language code from the name of a SAPI5 Natural voice
    # e.g. for "Microsoft Jorge Online (Natural) - Spanish (Mexico)" the language code "es-mx"
    # can be determined from the "- Spanish (Mexico)" suffix

    def __language_region_to_bcp47(lang_region: str) -> str:
        # Base language codes
        language_codes = {
            "albanian": "sq",
            "amharic": "am",
            "arabic": "ar",
            "azeri": "az",
            "bengali": "bn",
            "bosnian": "bs",
            "bulgarian": "bg",
            "burmese": "my",
            "cantonese": "zh",
            "catalan": "ca",
            "chinese": "zh",
            "croatian": "hr",
            "czech": "cs",
            "danish": "da",
            "dutch": "nl",
            "english": "en",
            "estonian": "et",
            "filipino": "tl",
            "finnish": "fi",
            "french": "fr",
            "georgian": "ka",
            "german": "de",
            "greek": "el",
            "gujarati": "gu",
            "hebrew": "he",
            "hindi": "hi",
            "hungarian": "hu",
            "icelandic": "is",
            "indonesian": "id",
            "inuktitut_latin": "iu-latn", # Inuktitut (Latin, Canada)
            "inuktitut_syllabics": "iu", # Inuktitut (Syllabics, Canada)
            "irish": "ga",
            "italian": "it",
            "japanese": "ja",
            "kannada": "kn",
            "kazakh": "kk",
            "khmer": "km",
            "korean": "ko",
            "lao": "lo",
            "latvian": "lv",
            "lithuanian": "lt",
            "macedonian": "mk",
            "malay": "ms",
            "malayalam": "ml",
            "maltese": "mt",
            "marathi": "mr",
            "mongolian": "mn",
            "nepali": "ne",
            "norwegian": "no",
            "pashto": "ps",
            "persian": "fa",
            "polish": "pl",
            "portuguese": "pt",
            "romanian": "ro",
            "russian": "ru",
            "serbian": "sr",
            "sinhala": "si",
            "slovak": "sk",
            "slovenian": "sl",
            "somali": "so",
            "spanish": "es",
            "swahili": "sw",
            "swedish": "sv",
            "tamil": "ta",
            "telugu": "te",
            "thai": "th",
            "turkish": "tr",
            "ukrainian": "uk",
            "urdu": "ur",
            "uzbek": "uz",
            "vietnamese": "vi",
            "welsh": "cy",
            "afrikaans": "af",
            "azerbaijani": "az",
            "bangla": "bn",
            "inuktitut": "iu",
            "zulu": "zu"
        }

        # Region ? country code
        region_codes = {
            "afghanistan": "af",
            "albania": "al",
            "algeria": "dz",
            "argentina": "ar",
            "australia": "au",
            "austria": "at",
            "azerbaijan": "az",
            "bahrain": "bh",
            "bangladesh": "bd",
            "belgium": "be",
            "bokmål norway": "no",
            "bolivia" :"bo",
            "bosnia and herzegovina": "ba",
            "bosnia": "ba",
            "brazil": "br",
            "bulgaria": "bg",
            "cambodia": "kh",
            "canada": "ca",
            "chile": "cl",
            "china": "cn",
            "colombia": "co",
            "costa rica" :"cr",
            "croatia": "hr",
            "cuba" :"cu",
            "czech republic": "cz",
            "czech": "cz",
            "denmark": "dk",
            "dominican republic" :"do",
            "ecuador" :"ec",
            "egypt": "eg",
            "el salvador" :"sv",
            "equatorial guinea": "gq",
            "estonia": "ee",
            "ethiopia": "et",
            "finland": "fi",
            "france": "fr",
            "georgia": "ge",
            "germany": "de",
            "great britain": "gb",
            "greece": "gr",
            "guatemala" :"gt",
            "honduras" :"hn",
            "hong kong sar": "hk",
            "hong kong": "hk",
            "hongkong": "hk",
            "hungary": "hu",
            "iceland": "is",
            "india": "in",
            "indonesia": "id",
            "iran": "ir",
            "iraq": "iq",
            "ireland": "ie",
            "israel": "il",
            "italy": "it",
            "japan": "jp",
            "jordan": "jo",
            "kazakhstan": "kz",
            "kenya": "ke",
            "korea": "kr",
            "kuwait": "kw",
            "laos": "la",
            "latvia": "lv",
            "lebanon": "lb",
            "libya": "ly",
            "lithuania": "lt",
            "macau": "mo",
            "malaysia": "my",
            "malta": "mt",
            "mexico": "mx",
            "mongolia": "mn",
            "morocco": "ma",
            "myanmar": "mm",
            "nepal": "np",
            "netherlands": "nl",
            "new zealand": "nz",
            "nicaragua" :"ni",
            "nigeria": "ng",
            "north macedonia": "mk",
            "norway": "no",
            "oman": "om",
            "pakistan": "pk",
            "panama" :"pa",
            "paraguay" :"py",
            "peru": "pe",
            "philippines": "ph",
            "poland": "pl",
            "portugal": "pt",
            "puerto rico" :"pr",
            "qatar": "qa",
            "romania": "ro",
            "russia": "ru",
            "saudi arabia": "sa",
            "serbia": "rs",
            "singapore": "sg",
            "slovakia": "sk",
            "slovenia": "si",
            "somalia": "so",
            "south africa": "za",
            "spain": "es",
            "sri lanka": "lk",
            "sweden": "se",
            "switzerland": "ch",
            "syria": "sy",
            "taiwan": "tw",
            "tanzania": "tz",
            "thailand": "th",
            "tunisia": "tn",
            "turkey": "tr",
            "türkiye": "tr",
            "ukraine": "ua",
            "united arab emirates": "ae",
            "united kingdom": "gb",
            "united states": "us",
            "uruguay" :"uy",
            "uzbekistan": "uz",
            "venezuela" :"ve",
            "vietnam": "vn",
            "yemen": "ye",

            # Special cases
            "cantonese traditional": "cn",
            "mainland": "cn",
            "northeastern mandarin": "cn",
            "taiwanese mandarin": "tw",
            "zhongyuan mandarin shaanxi": "cn"
        }

        if lang_region.endswith('(Preview)'):
            # Special case: if the string ends with "(Preview)", remove the suffix
            lang_region = lang_region[:-9] 

        s = lang_region.strip().lower()

        s = re.sub(r"\s+", " ", s)

        # Split "language (region)" or "language (variant, region)"
        m = re.match(r"([^(]+)\((.+)\)", s)
        if not m:
            # No region: just language
            base_lang = language_codes.get(s, "unknown")

            return base_lang

        lang_name = m.group(1).strip()          # 'spanish'
        region_part = m.group(2).strip()        # 'peru' or 'traditional, taiwan'

        base_lang = language_codes.get(lang_name, "unknown")

        # Region may have commas: 'traditional, taiwan' -> 'taiwan'
        region_tokens = [t.strip() for t in region_part.split(",")]

        # Heuristic: last token is the country/region
        region_name = region_tokens[-1]

        country = region_codes.get(region_name, "unknown")

        if base_lang == "unknown":
            print(f"        Warning: Language not recognised for {lang_region}")
            return ""

        if country == "unknown":
           print(f"        Warning: Country not recognised for {lang_region}")
           return base_lang  # at least return language

        return f"{base_lang}-{country}"


    # Identify the language code given the name of a natural voice
    def __infer_language_from_voice_name(voice_name: str) -> str:
        # e.g.:
        #
        # 'Microsoft Jorge Online (Natural) - Spanish (Mexico)' -> 'es-mx'
        # 'Microsoft Alex Online (Natural) - Spanish (Peru)'   -> 'es-pe'

        m = re.search(r"-\s*(.+)$", voice_name)
        if not m:
            return ""

        lang_region = m.group(1).strip()

        return SapiVoiceReader.__language_region_to_bcp47(lang_region)

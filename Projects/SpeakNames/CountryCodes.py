@staticmethod
def get_language_from_country(country):

    # TODO(SpeakNames): Add support for additional countries as required
    languages = {
        'Argentina':          'es-AR',
        'Australia':          'en-AU',
        'Austria':            'de-AT',
        'Bangladesh':         'bn-IN',
        'Belgium':            'nl-BE',  # TODO(SpeakNames): Also add fr-BE (French) and de-BE (German)
        'Bolivia':            'es-BO',
        'Brazil':             'pt-BR',
        'Burkina Faso':       'fr-BF',
        'Cambodia':           'km-KH',
        'Cameroon':           'fr-CM',
        'Canada':             'en-CA',  # TODO(SpeakNames): Also add fr-CA (French)
        'Chile':              'es-CL',
        'China':              'zh-CN',
        'Colombia':           'es-CO',
        'Costa Rica':         'es-CR',
        'Cuba':               'es-CU',
        'Dominican Republic': 'es-DO',
        'Ecuador':            'es-EC',
        'El Salvador':        'es-SV',
        'Estonian':           'et-EE',
        'Finland':            'fi-FI',
        'Fiji':               'en-FJ',
        'France':             'fr-FR',
        'Georgia':            'ka-GE',
        'Germany':            'de-DE',
        'Guatemala':          'es-GT',
        'Honduras':           'es-HN',
        'Hungary':            'hu-HU',
        'India':              'hi-IN',
        'Iran':               'fa-IR',
        'Italy':              'it-IT',
        'Ivory Coast':        'fr-CI',
        'Jamaica':            'en-JM',
        'Japan':              'ja-JP',
        'Korea':              'ko-KR',
        'Latvia':             'lv-LV',
        'Lithuania':          'lt-LT',
        'Mexico':             'es-MX',
        'Myanmar':            'my-MM',
        'Nepal':              'ne-NP',
        'Netherlands':        'nl-NL',
        'Nicaragua':          'es-NI',
        'Pakistan':           'ur-PK',
        'Peru':               'es-PE',
        'Philippines':        'en-PH',
        'Poland':             'pl-PL',
        'Romania':            'ro-RO',
        'Russia':             'ru-RU',
        'Singapore':          'en-SG',
        'South Africa':       'en-ZA',
        'Spain':              'es-ES',
        'Switzerland':        'de-CH',  # TODO(SpeakNames): Also add fr-CH (French) and it-CH (Italian)
        'Taiwan':             'zh-TW',
        'Thailand':           'th-TH',
        'Turkey':             'tr-TR',
        'Philippines':        'en-PH',
        'United Kingdom':     'en-GB',
        'Uruguay':            'es-UR',
        'USA':                'en-US',
        'Venezuela':          'es-VE',
        'Vietnam':            'vi-VN'
    }

    if country in languages:
        return languages[country]

    return ''

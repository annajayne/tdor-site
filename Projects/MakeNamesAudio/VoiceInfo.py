class VoiceInfo:
    def get_number(self):
        return self.number

    def set_number(self, number):
        self.number = number


    def get_id(self):
        return self.id

    def set_id(self, id):
        self.id = id


    def get_name(self):
        return self.name

    def set_name(self, name):
        self.name = name


    def get_gender(self):
        return self.gender

    def set_gender(self, gender):
        self.gender = gender


    def get_language(self):
        return self.language

    def set_language(self, language):
        self.language = language


    def set_languages(self, languages):
        self.language = languages[0] # We only support the first one

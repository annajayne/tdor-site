class VoiceInfo:
    _engine = ""
    _number = 0
    _id = ""
    _name = ""
    _language = ""
    _enabled = True


    def get_engine(self):
        return self._engine

    def set_engine(self, engine):
        self._engine = engine


    def get_number(self):
        return self._number

    def set_number(self, number):
        self._number = number


    def get_id(self):
        return self._id

    def set_id(self, id):
        self._id = id


    def get_name(self):
        return self._name

    def set_name(self, name):
        self._name = name


    def get_language(self):
        return self._language

    def set_language(self, language):
        self._language = language


    def get_enabled(self):
        return self._enabled

    def set_enabled(self, enabled):
        self._enabled = enabled

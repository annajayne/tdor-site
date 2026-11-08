class Report:
    _uid = ""
    _name = ""
    _age = ""
    _birthdate = ""
    _photo_filename = ""
    _photo_source = ""  
    _date = ""
    _tdor_list_ref = ""
    _location = ""

    # TODO(PyTdorLib): Add address, locality, municipality and state fields

    _country = ""
    _latitude = ""
    _longitude = ""
    _category = ""
    _cause = ""
    _description = ""

    # TODO(PyTdorLib): Add a tweet or microblog_post field

    _remarks = ""       # TGEU specific
    _source = ""        # TGEU specific
    _reported_by = ""   # TGEU specific

    _permalink = ""
    _uid = ""


    def empty(self):
        return (len(self._name) == 0)


    def get_uid(self):
        return self._uid

    def set_uid(self, uid):
        self._uid = uid


    def get_name(self):
        return self._name

    def set_name(self, name):
        self._name = name


    def get_age(self):
        return self._age

    def set_age(self, age):
        self._age = age


    def get_birthdate(self):
        return self._birthdate

    def set_birthdate(self, birthdate):
        self._birthdate = birthdate


    def get_photo_filename(self):
        return self._photo_filename

    def set_photo_filename(self, photo_filename):
        self._photo_filename = photo_filename


    def get_photo_source(self):
        return self._photo_source

    def set_photo_source(self, photo_source):
        self._photo_source = photo_source


    def get_date(self):
        return self._date

    def set_date(self, date):
        self._date = date


    def get_tdor_list_ref(self):
        return self._tdor_list_ref

    def set_tdor_list_ref(self, tdor_list_ref):
        self._tdor_list_ref = tdor_list_ref


    def get_location(self):
        return self._location

    def set_location(self, location):
        self._location = location

    # TODO(PyTdorLib): Add get/set_address(), get/set_locality(), get/set_municipality() and get/set_state()

    def get_country(self):
        return self._country

    def set_country(self, country):
        self._country = country


    def get_latitude(self):
        return self._latitude

    def set_latitude(self, latitude):
        self._latitude = latitude


    def get_longitude(self):
        return self._longitude

    def set_longitude(self, longitude):
        self._longitude = longitude


    def get_category(self):
        return self._category

    def set_category(self, category):
        self._category = category


    def get_cause(self):
        return self._cause

    def set_cause(self, cause):
        self._cause = cause


    def get_description(self):
        return self._description

    def set_description(self, description):
        self._description = description


    def get_remarks(self):
        return self._remarks

    def set_remarks(self, remarks):
        self._remarks = remarks


    def get_source(self):
        return self._source

    def set_source(self, source):
        self._source = source


    def get_reported_by(self):
        return self._reported_by

    def set_reported_by(self, reported_by):
        self._reported_by = reported_by


    # TODO(PyTdorLib): Add get/set_tweet() or get/set_microblog_post()
     
    def get_permalink(self):
        return self._permalink

    def set_permalink(self, permalink):
        self._permalink = permalink


    def get_report_summary(report):
        category_cause = report.get_category() + "/" + report.get_cause()
        location = report.get_location().rstrip(",")
    
        summary = f"{report.get_name()} ({report.get_date()} - {location}, {report.get_country()}) [{category_cause}]"

        return summary


    # Build the description field from the (TGEU specific) remarks, reported by and source fields
    def build_description(self):
        description         = self.get_remarks()
        reported_by         = self.get_reported_by()

        if reported_by and (reported_by != 'unknown'):
            description     +=  ('\n\n' + reported_by)

        if self.get_source():
            description     +=  ('\n\n' + self.get_source())

        self.set_description(description)

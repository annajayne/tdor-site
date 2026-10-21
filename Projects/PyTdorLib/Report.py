class Report:
    _uid = ""
    _name = ""
    _age = ""
    _date = ""
    _location = ""      # TODO: support separate City & State/Province fields
    _country = ""
    _category = ""
    _cause = ""
    _remarks = ""       # TODO: rename to Description
    _source = ""        # TGEU specific
    _reported_by = ""   # TGEU specific
    _permalink = ""
    _uid = ""

    _data = None


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


    def get_date(self):
        return self._date

    def set_date(self, date):
        self._date = date


    def get_location(self):
        return self._location

    def set_location(self, location):
        self._location = location


    def get_country(self):
        return self._country

    def set_country(self, country):
        self._country =country


    def get_category(self):
        return self._category

    def set_category(self, category):
        self._category = category


    def get_cause(self):
        return self._cause

    def set_cause(self, cause):
        self._cause = cause


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

    
    def get_permalink(self):
        return self._permalink

    def set_permalink(self, permalink):
        self._permalink = permalink


    def get_report_summary(report):
        category_cause = report.get_category() + "/" + report.get_cause()
        location = report.get_location().rstrip(",")
    
        summary = f"{report.get_name()} ({report.get_date()} - {location}, {report.get_country()}) [{category_cause}]"

        return summary



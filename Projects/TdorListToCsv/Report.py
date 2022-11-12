class Report:
    def __init__(self):
        self.set_age('')
        self.set_date('')
        self.set_cause('')
        self.set_location('')
        self.set_remarks('')
        self.set_source('')
        self.set_reported_by('')


    def clear():
        self.data = []

    def empty(self):
        return (len(self.name) == 0)


    def get_name(self):
        return self.name

    def set_name(self, name):
        self.name = name


    def get_age(self):
        return self.age

    def set_age(self, age):
        self.age = age


    def get_date(self):
        return self.date

    def set_date(self, date):
        self.date = date


    def get_location(self):
        return self.location

    def set_location(self, location):
        self.location = location


    def get_cause(self):
        return self.cause

    def set_cause(self, cause):
        self.cause = cause


    def get_remarks(self):
        return self.remarks

    def set_remarks(self, remarks):
        self.remarks = remarks


    def get_source(self):
        return self.source

    def set_source(self, source):
        self.source = source


    def get_reported_by(self):
        return self.reported_by

    def set_reported_by(self, reported_by):
        self.reported_by = reported_by

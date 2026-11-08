import os
import sys
import unittest

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '../PyTdorLib'))

from Report import Report
from ReportsCsvFileWriter import ReportsCsvFileWriter


class Test_ReportsCsvFileWriter(unittest.TestCase):
    def setUp(self):
        self.reports = []

        report = Report()

        report.set_name('Name1')
        report.set_age('Name1_age')
        report.set_birthdate("Name1_birthdate")
        report.set_photo_filename("Name1_photo")
        report.set_photo_source("Name1_photo_source")
        report.set_date('10/01/2015')
        report.set_tdor_list_ref('tgeu/2015-01-10/Alvari')
        report.set_location('Vitoria (Brazil)')
        report.set_latitude("10")
        report.set_longitude("-100")
        report.set_cause('not reported')
        report.set_description('Name1_description')

        self.reports.append(report)


    def _get_test_file_path(self, filename):
        data_path = os.path.join(os.path.dirname(__file__), "data")

        return os.path.join(data_path, filename)


    def test_get_csv_header(self):
        writer          = ReportsCsvFileWriter();

        self.assertEqual(writer.get_header(), 'Name,Age,Birthdate,Photo,Photo source,Date,TDoR list ref,Address,Locality,Town/City/Municipality,State/Province,Country,Latitude,Longitude,Category,Cause of death,Description,Tweet,Permalink')


    def test_get_csv_entry(self):
        writer          = ReportsCsvFileWriter();

        report = self.reports[0]

        expected_entry  = "Name1,Name1_age,Name1_birthdate,Name1_photo,Name1_photo_source,10/01/2015,tgeu/2015-01-10/Alvari,,,Vitoria,,Brazil,10,-100,uncategorised,not reported,Name1_description,,"

        actual_entry    = writer.get_entry(report)

        if (actual_entry != expected_entry):
            # Text is long, so print in full to aid diagnosing test failures

            print('\r\n\r\nExpected:\r\n')
            print(expected_entry)

            print('\r\n\r\nActual:\r\n')
            print(actual_entry)

        self.assertEqual(writer.get_entry(report), expected_entry)


    def test_write_csv_file(self):
        writer          = ReportsCsvFileWriter();

        pathname        = self._get_test_file_path("ReportsCsvFileWriterTest.csv")

        if (os.path.isfile(pathname) ):
            os.remove(pathname)

        writer.write(self.reports, pathname)

        self.assertTrue(os.path.isfile(pathname) )

        f = open(pathname, encoding="utf-8")

        file_contents = f.read()
        f.close()

        os.remove(pathname)

        # TODO(PyTdorLib): Add more detailed checks of the file contents, e.g. check that the header and entry are present and correct
        self.assertTrue(len(file_contents) > 0)

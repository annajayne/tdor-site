import os
import unittest

from Report import Report
from ReportsCsvFileReader import ReportsCsvFileReader


class Test_ReportsCsvFileReader(unittest.TestCase):
    def setUp(self):
        self.reader = ReportsCsvFileReader()


    def _get_test_file_path(self, filename):
        data_path = os.path.join(os.path.dirname(__file__), "data")

        return os.path.join(data_path, filename)


    def _read_reports(self, filename):
        path = self._get_test_file_path(filename)

        return self.reader.read(path)


    def test_regular_file_format_report_count(self):
        # Test that the read method returns a list of Report objects with the expected properties

        reports = self._read_reports("TestReportsCsvFileReader_RegularFormat.csv")

        self.assertIsInstance(reports, list)

        self.assertEqual(len(reports), 2)

        for report in reports:
            self.assertIsInstance(report, Report)


    def test_regular_file_format_report_contents(self):
        # Test that the read method returns a list of Report objects with the expected properties
        # when a tdor-data archive file is read as input.
        #
        # These have separate address, locality, municipality and state fields
        # (of which the municipality and state fields are read into the Report
        # object and combined into a single "location" field).

        reports = self._read_reports("TestReportsCsvFileReader_RegularFormat.csv")
        self.assertIsInstance(reports, list)

        report = reports[0]

        self.assertEqual(report.get_name(), "Name1")
        self.assertEqual(report.get_age(), "Name1_age")
        self.assertEqual(report.get_date(), "01-Jan-26")
        self.assertEqual(report.get_tdor_list_ref(), "Name1_tdor_list_ref")
        self.assertEqual(report.get_address(), "Name1_address")
        self.assertEqual(report.get_locality(), "Name1_locality")
        self.assertEqual(report.get_municipality(), "Name1_municipality")
        self.assertEqual(report.get_state(), "Name1_state")
        self.assertEqual(report.get_location(), "Name1_municipality, Name1_state")
        self.assertEqual(report.get_country(), "Name1_country")
        self.assertEqual(report.get_latitude(), "10")
        self.assertEqual(report.get_longitude(), "-100")
        self.assertEqual(report.get_category(), "Name1_category")
        self.assertEqual(report.get_cause(), "Name1_cause")
        self.assertEqual(report.get_description(), "Name1_description")
        self.assertEqual(report.get_post_text(), "Name1_tweet")
        self.assertEqual(report.get_permalink(), "https://tdor.translivesmatter.info/test/name1_12345678")
        self.assertEqual(report.get_uid(), "12345678")


    def test_exported_file_format_report_contents(self):
        # Test that the read method returns a list of Report objects with the expected properties
        # when an exported file (Reports | Download Data) from the site is read as input.
        #
        # These have additional fields but currently only have a single "location" field rather
        # than separate address, locality, municipality and state fields.

        reports = self._read_reports("TestReportsCsvFileReader_ExtendedFormat.csv")
        self.assertIsInstance(reports, list)

        report = reports[0]

        self.assertEqual(report.get_name(), "Name1")
        self.assertEqual(report.get_age(), "Name1_age")
        self.assertEqual(report.get_birthdate(), "Name1_birthdate")
        self.assertEqual(report.get_photo_filename(), "Name1_photo")
        self.assertEqual(report.get_photo_source(), "Name1_photo_source")
        self.assertEqual(report.get_date(), "01-Jan-26")
        self.assertEqual(report.get_tdor_list_ref(), "Name1_tdor_list_ref")
        self.assertEqual(report.get_address(), "")
        self.assertEqual(report.get_locality(), "")
        self.assertEqual(report.get_municipality(), "")
        self.assertEqual(report.get_state(), "")
        self.assertEqual(report.get_location(), "Name1_location")
        self.assertEqual(report.get_country(), "Name1_country")
        self.assertEqual(report.get_latitude(), "10")
        self.assertEqual(report.get_longitude(), "-100")
        self.assertEqual(report.get_category(), "Name1_category")
        self.assertEqual(report.get_cause(), "Name1_cause")
        self.assertEqual(report.get_description(), "Name1_description")
        self.assertEqual(report.get_post_text(), "Name1_tweet")
        self.assertEqual(report.get_permalink(), "https://tdor.translivesmatter.info/test/name1_12345678")
        self.assertEqual(report.get_uid(), "12345678")

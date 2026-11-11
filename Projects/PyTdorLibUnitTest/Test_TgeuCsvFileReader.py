import os
import sys
import unittest

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '../PyTdorLib'))

from Report import Report
from TgeuCsvFileReader import TgeuCsvFileReader


class Test_TgeuCsvFileReader(unittest.TestCase):
    def setUp(self):
        self.reader = TgeuCsvFileReader()


    def _get_test_file_path(self, filename):
        # These tests use the data folder from the TdorsListToCsv project, which contains the test files
        data_path = os.path.join(os.path.dirname(__file__), "../TdorListToCsv/data")

        return os.path.join(data_path, filename)


    def _read_reports(self, filename):
        input_path = self._get_test_file_path(filename)

        return self.reader.read(input_path, "tgeu")


    def test_tdor2021_count(self):
        reports = self._read_reports("TvT_TMM_TDoR2021_Namelist.csv")

        count = len(reports)

        self.assertEqual(count, 374)


    def test_tdor2021_report_content(self):
        reports = self._read_reports("TvT_TMM_TDoR2021_Namelist.csv")
        
        report = reports[0]

        # TODO(PyTdorLib): The name has a different character for the opening quote. We should probably transform such quotes when loading the file
        self.assertEqual(report.get_name(),             'Nathalia Leticia Barbosa da Fonseca (“Bia")')
        self.assertEqual(report.get_age(),              "26")
        self.assertEqual(report.get_date(),             "2020-10-03")
        self.assertEqual(report.get_tdor_list_ref(),    'tgeu/2020-10-03/Nathalia Leticia Barbosa da Fonseca (“Bia")')
        self.assertEqual(report.get_location(),         "Paraná (Brazil)")
        self.assertEqual(report.get_country(),          "Brazil")
        self.assertEqual(report.get_category(),         "violence")
        self.assertEqual(report.get_cause(),            "shot")
        self.assertEqual(report.get_remarks(),          "Natália was shot at least 8 times on the afternoon of Saturday 3rd October.")
        self.assertEqual(report.get_source(),           "Trans lives matter & http://portaldotossinha.com.br/travesti-e-assassinada-a-tiros-em-sao-pedro-do-ivai/")

        # The description field is generated from the remarks, reported by and source fields
        self.assertEqual(report.get_description(),      report.get_remarks() + "\n\n" + report.get_source())


    def test_tdor2022_count(self):
        reports = self._read_reports("TvT_TMM_TDoR2022_Namelist.csv")

        count = len(reports)

        self.assertEqual(count, 327)


    def test_tdor2023_count(self):
        reports = self._read_reports("TvT_TMM_TDoR2023_Namelist.csv")

        count = len(reports)

        self.assertEqual(count, 320)


    def test_tdor2024_count(self):
        reports = self._read_reports("TGEU-TMM-TDoR2024-Namelist.csv")

        count = len(reports)

        self.assertEqual(count, 350)


    def test_tdor2025_count(self):
        reports = self._read_reports("2025-TMM-Namelist.csv")

        count = len(reports)

        self.assertEqual(count, 281)


    def test_tdor2026_count(self):
        reports = self._read_reports("2026-TMM-Namelist.csv")

        count = len(reports)

        self.assertEqual(count, 273)

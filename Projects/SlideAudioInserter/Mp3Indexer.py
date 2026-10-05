import os

from pathlib import Path


class Mp3Indexer:
    def __init__(self, directory):
        self.directory = Path(directory).resolve()
        self.index = {}
        self.index_files()


    def index_files(self):
        for filename in os.listdir(self.directory):
            if filename.endswith('.mp3'):
                name = filename[:-4]
                items = name.split('_')
                uid = items[-1]
                if len(uid) > 0:
                    self.index[uid] = os.path.join(self.directory, filename)

                    print(f"  Found audio file {filename} [UID: {uid}]")


    def get_file_by_uid(self, uid):
        return self.index.get(uid)

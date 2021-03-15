/*
 * Support script for uploading zipfiles.
 */


$(document).ready(function()
{
    $("#zipfileUpload").on('change', function()
    {
        // Clear the output log text when a new file is selected (but before it is uploaded)
        document.getElementById("output").innerHTML = ""; 
         
        // Get the number of selected files
        var files = $(this)[0].files;

        var countFiles = files.length;

        // Loop through the archive files selected for upload and list their name and size
        var placeholder = $("#zipfile-contents-placeholder");

        placeholder.empty();

        for (var i = 0; i < countFiles; i++)
        {
            var imgPath = files.item(i).name;
            var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();

            if (extn == "zip")
            {
                var preview = document.getElementById("zipfile-contents-placeholder");

                var p = document.createElement("p");

                p.textContent = imgPath + " (" + files.item(i).size + " bytes)";

                preview.appendChild(p);
            }
            else
            {
                alert("Please select only zipfiles.");
            }
        }

        var submit = document.getElementById("submit");
        submit.style.display = "block"
    });
});

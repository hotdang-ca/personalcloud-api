<?php

use Illuminate\Http\Response;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/browse', function () use ($app) {
  $page = <<<HTML
  <!DOCTYPE html>
  <html>
    <head>
      <title>View All Files</title>
      <style>
        table {
          width: 80%;
          margin: 0px auto;
        }

        td, th {
          width: 25%;
          height: 56px;
        }

        .file {
          padding: 8px 0;
          background-color: #efefef;
          border: 1px solid #a0a0a0;
          justify-content: space-around;
          align-items: flex-start;
        }

        .file > a {
          font-size: 1.2rem;
        }

        .preview {
          height: 40px;
          width: auto;
        }
      </style>
    </head>
    <body>
      <h1>All files</h1>
      <table>
        <thead>
          <tr>
            <th>Preview</th>
            <th>Filename</th>
            <th>Uploader</th>
            <th>Extension</th>
          </tr>
        </thead>
        <tbody id="files-table"></tbody>
      </table>

      <script>
        var container = document.getElementById('files-table');
        var xhr = new XMLHttpRequest();
        var method = "GET"
        var url = "/api/v1/files";
        xhr.open(method, url, true);
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (!response) {
              alert('Read error. Refresh.');
            }

            response.files.forEach(function(file) {

              console.log('file', file);

              var fileDescriptionNode = document.createElement('tr');
              fileDescriptionNode.setAttribute('class', 'file');

              // link
              var fileDescriptionLinkContainer = document.createElement('td');
              var fileDescriptionLink = document.createElement('a');
              fileDescriptionLink.href = '/file/' + file.storage_name;
              var fileDescriptionLinkText = document.createTextNode(file.original_name);
              fileDescriptionLink.appendChild(fileDescriptionLinkText);
              fileDescriptionLinkContainer.appendChild(fileDescriptionLink);

              // extension
              var fileExtensionContainer = document.createElement('td');
              var fileExtensionText = document.createTextNode(file.extension);
              fileExtensionContainer.appendChild(fileExtensionText);

              // ip
              var fileIpAddressContainer = document.createElement('td');
              var fileIpAddressText = document.createTextNode(file.uploader_ip);
              fileIpAddressContainer.appendChild(fileIpAddressText);

              // preview...
              var fileImageContainer = document.createElement('td');
              if (['jpg', 'png', 'gif'].includes(file.extension)) {
                var fileImage = document.createElement('img');
                fileImage.src = '/file/' + file.storage_name;
                fileImage.setAttribute('class', 'preview');
                fileImageContainer.appendChild(fileImage);
              }

              fileDescriptionNode.appendChild(fileImageContainer);
              fileDescriptionNode.appendChild(fileDescriptionLinkContainer);
              fileDescriptionNode.appendChild(fileIpAddressContainer);
              fileDescriptionNode.appendChild(fileExtensionContainer);

              container.appendChild(fileDescriptionNode);

            });
          }
        }
        xhr.send();
      </script>
    </body>
  </html>
HTML;

return response($page, 200);
});

$app->get('/upload', function () use ($app) {
  $page = <<<HTML
  <p>Upload a file</p>
  <form action="/api/v1/file" method="post" enctype="multipart/form-data">
    <input type="file" name="file" /><br/>
    <input type="submit" value="Send'r on Up!" />
  </form>
  <hr>
  <p>Ajax Upload</p>
  <form id="ajaxform" action="/api/v1/file" method="post" enctype="multipart/form-data">
    <input type="file" name="file" /><br/>
    <button id="ajaxSubmitButton">Submit it Ajax-y!</button>
  </form>

  <script type="text/javascript">
  "use strict";

  /*\
  |*|
  |*|  :: XMLHttpRequest.prototype.sendAsBinary() Polyfill ::
  |*|
  |*|  https://developer.mozilla.org/en-US/docs/DOM/XMLHttpRequest#sendAsBinary()
  \*/

  if (!XMLHttpRequest.prototype.sendAsBinary) {
    XMLHttpRequest.prototype.sendAsBinary = function(sData) {
      var nBytes = sData.length, ui8Data = new Uint8Array(nBytes);
      for (var nIdx = 0; nIdx < nBytes; nIdx++) {
        ui8Data[nIdx] = sData.charCodeAt(nIdx) & 0xff;
      }
      /* send as ArrayBufferView...: */
      this.send(ui8Data);
      /* ...or as ArrayBuffer (legacy)...: this.send(ui8Data.buffer); */
    };
  }

  /*\
  |*|
  |*|  :: AJAX Form Submit Framework ::
  |*|
  |*|  https://developer.mozilla.org/en-US/docs/DOM/XMLHttpRequest/Using_XMLHttpRequest
  |*|
  |*|  This framework is released under the GNU Public License, version 3 or later.
  |*|  http://www.gnu.org/licenses/gpl-3.0-standalone.html
  |*|
  |*|  Syntax:
  |*|
  |*|   AJAXSubmit(HTMLFormElement);
  \*/

  var AJAXSubmit = (function () {

    function ajaxSuccess () {
      /* console.log("AJAXSubmit - Success!"); */
      console.log(this.responseText);
      /* you can get the serialized data through the "submittedData" custom property: */
      /* console.log(JSON.stringify(this.submittedData)); */
    }

    function submitData (oData) {
      /* the AJAX request... */
      var oAjaxReq = new XMLHttpRequest();
      oAjaxReq.submittedData = oData;
      oAjaxReq.onload = ajaxSuccess;
      if (oData.technique === 0) {
        /* method is GET */
        oAjaxReq.open("get", oData.receiver.replace(/(?:\?.*)?$/, oData.segments.length > 0 ? "?" + oData.segments.join("&") : ""), true);
        oAjaxReq.send(null);
      } else {
        /* method is POST */
        oAjaxReq.open("post", oData.receiver, true);
        if (oData.technique === 3) {
          /* enctype is multipart/form-data */
          var sBoundary = "---------------------------" + Date.now().toString(16);
          oAjaxReq.setRequestHeader("Content-Type", "multipart\/form-data; boundary=" + sBoundary);
          oAjaxReq.sendAsBinary("--"
          + sBoundary
          + "\\r\\n"
          + oData.segments.join("--" + sBoundary + "\\r\\n")
          + "--"
          + sBoundary
          + "--\\r\\n");

        } else {
          /* enctype is application/x-www-form-urlencoded or text/plain */
          oAjaxReq.setRequestHeader("Content-Type", oData.contentType);
          oAjaxReq.send(oData.segments.join(oData.technique === 2 ? "\\r\\n" : "&"));
        }
      }
    }

    function processStatus (oData) {
      if (oData.status > 0) { return; }
      /* the form is now totally serialized! do something before sending it to the server... */
      /* doSomething(oData); */
      /* console.log("AJAXSubmit - The form is now serialized. Submitting..."); */
      submitData (oData);
    }

    function pushSegment (oFREvt) {
      this.owner.segments[this.segmentIdx] += oFREvt.target.result + "\\r\\n";
      this.owner.status--;
      processStatus(this.owner);
    }

    function plainEscape (sText) {
      /* how should I treat a text/plain form encoding? what characters are not allowed? this is what I suppose...: */
      /* "4\3\7 - Einstein said E=mc2" ----> "4\\3\\7\ -\ Einstein\ said\ E\=mc2" */
      return sText.replace(/[\s\=\\\\]/g, "\\\\$&");
    }

    function SubmitRequest (oTarget) {
      var nFile, sFieldType, oField, oSegmReq, oFile, bIsPost = oTarget.method.toLowerCase() === "post";
      /* console.log("AJAXSubmit - Serializing form..."); */
      this.contentType = bIsPost && oTarget.enctype ? oTarget.enctype : "application\/x-www-form-urlencoded";
      this.technique = bIsPost ? this.contentType === "multipart\/form-data" ? 3 : this.contentType === "text\/plain" ? 2 : 1 : 0;
      this.receiver = oTarget.action;
      this.status = 0;
      this.segments = [];
      var fFilter = this.technique === 2 ? plainEscape : escape;
      for (var nItem = 0; nItem < oTarget.elements.length; nItem++) {
        oField = oTarget.elements[nItem];
        if (!oField.hasAttribute("name")) { continue; }
        sFieldType = oField.nodeName.toUpperCase() === "INPUT" ? oField.getAttribute("type").toUpperCase() : "TEXT";
        if (sFieldType === "FILE" && oField.files.length > 0) {
          if (this.technique === 3) {
            /* enctype is multipart/form-data */
            for (nFile = 0; nFile < oField.files.length; nFile++) {
              oFile = oField.files[nFile];
              oSegmReq = new FileReader();
              /* (custom properties:) */
              oSegmReq.segmentIdx = this.segments.length;
              oSegmReq.owner = this;
              /* (end of custom properties) */
              oSegmReq.onload = pushSegment;
              this.segments.push("Content-Disposition: form-data; name=\"" + oField.name + "\"; filename=\""+ oFile.name + "\"\\r\\nContent-Type: " + oFile.type + "\\r\\n\\r\\n");
              this.status++;
              oSegmReq.readAsBinaryString(oFile);
            }
          } else {
            /* enctype is application/x-www-form-urlencoded or text/plain or method is GET: files will not be sent! */
            for (nFile = 0; nFile < oField.files.length; this.segments.push(fFilter(oField.name) + "=" + fFilter(oField.files[nFile++].name)));
          }
        } else if ((sFieldType !== "RADIO" && sFieldType !== "CHECKBOX") || oField.checked) {
          /* field type is not FILE or is FILE but is empty */
          this.segments.push(
            this.technique === 3 ? /* enctype is multipart/form-data */
              "Content-Disposition: form-data; name=\"" + oField.name + "\"\\r\\n\\r\\n" + oField.value + "\\r\\n"
            : /* enctype is application/x-www-form-urlencoded or text/plain or method is GET */
              fFilter(oField.name) + "=" + fFilter(oField.value)
          );
        }
      }
      processStatus(this);
    }

    return function (oFormElement) {
      if (!oFormElement.action) { return; }
      new SubmitRequest(oFormElement);
    };

  })();

  document.getElementById('ajaxSubmitButton').addEventListener("click", function() {
    var form = document.getElementById('ajaxform');
    AJAXSubmit(form);
  });

  document.getElementById('ajaxform').addEventListener("submit", function(e) {
    console.log("Form is submitting.");
    e.preventDefault();
  });

  </script>
HTML;

  return response($page, 200);
});

$app->get('/file/{filename}', 'FilesController@fetch');

$app->group(['prefix' => 'api/v1'], function () use ($app) {
  $app->get('/about', function () use ($app) {
      return response()->json(["version" => $app->version()]);
  });
  $app->get('/files', 'FilesController@list');
  $app->get('/file/{filename}', 'FilesController@info');
  $app->post('/file', 'FilesController@upload');
});

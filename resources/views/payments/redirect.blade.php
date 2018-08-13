<html>
<head>
    <script type="application/javascript">
        function loadthis(){
            var formData = JSON.parse('{!! $formData !!}');

            var form = document.createElement("form");
            form.setAttribute("method", formData.form_attributes.method);
            form.setAttribute("action", formData.form_attributes.action);

            for(var key in formData.form_inputs) {
                if(formData.form_inputs.hasOwnProperty(key)) {
                    var hiddenField = document.createElement("input");
                    hiddenField.setAttribute("type", "hidden");
                    hiddenField.setAttribute("name", key);
                    hiddenField.setAttribute("value", formData.form_inputs[key]);

                    form.appendChild(hiddenField);
                }
            }
            document.body.appendChild(form);
            form.submit();
        }

    </script>
</head>
<body onload="loadthis()"></body>
</html>

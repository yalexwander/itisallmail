IAM = {}

IAM.normal_editor = "emacsclient -c -t"
IAM.script_editor = "php neomutt/utils/dumb_editor/editor.php"
IAM.sendmail = "php scripts/sendmail.php"

IAM.set_script_editor = function(editor_param)
   editor_param = editor_param or ""
   mutt.set("editor", IAM.script_editor .. " "  .. editor_param)
   mutt.set("include", "no")
end

IAM.set_normal_editor = function()
   mutt.set("include", "ask-yes")
   mutt.set("editor", IAM.normal_editor)
end

IAM.set_sendmail_args = function(args)
   mutt.set("sendmail", IAM.sendmail .. " " .. args)
end


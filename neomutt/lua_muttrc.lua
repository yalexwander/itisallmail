IAM = {}

IAM.normal_editor = "nano"
IAM.script_editor = "php neomutt/utils/dumb_editor/editor.php"

IAM.set_script_editor = function(editor_param)
   editor_param = editor_param or ""
   mutt.set("editor", IAM.script_editor .. " "  .. editor_param)
   mutt.set("include", "no")
end

IAM.set_normal_editor = function()
   mutt.set("include", "ask-yes")
   mutt.set("editor", IAM.normal_editor)
end


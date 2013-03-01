/**
 @preserve CLEditor WYSIWYG HTML Editor v1.3.0
 http://premiumsoftware.net/cleditor
 requires jQuery v1.4.2 or later

 Copyright 2010, Chris Landowski, Premium Software, LLC
 Dual licensed under the MIT or GPL Version 2 licenses.
*/

// ==ClosureCompiler==
// @compilation_level SIMPLE_OPTIMIZATIONS
// @output_file_name jquery.cleditor.min.js
// ==/ClosureCompiler==

var cli18n_en = {
	showRichText: 'Show Rich Text',
	selectionRequired: 'A selection is required when inserting a link.',
	execError1: 'Error executing the ',
	execError2: ' command.',
	submit: 'Submit',
	enterUrl: 'Enter URL',
	pasteContent: 'Paste your content here and click submit.',
	paragraph: 'Paragraph',
	header: 'Header',
	//buttons
	bold: '',
	italic: '',
	underline: '',
	strikethrough: '',
	subscript: '',
	superscript: '',
	font: '',
	size: 'Font Size',
	style: '',
	color: 'Font Color',
	highlight: 'Text Highlight Color',
	removeformat: 'Remove Formatting',
	bullets: '',
	numbering: '',
	outdent: '',
	indent: '',
	alignleft: 'Align Text Left',
	center: '',
	alignright: 'Align Text Right',
	justify: '',
	undo: '',
	redo: '',
	rule: 'Insert Horizontal Rule',
	image: 'Insert Image',
	link: 'Insert Hyperlink',
	unlink: 'Remove Hyperlink',
	print: '',
	source: 'Show Source'
};

if (!cli18n) {
	var cli18n = cli18n_en;
}
	  
(function($) {

  //==============
  // jQuery Plugin
  //==============

  $.cleditor = {

    // Define the defaults used for all new cleditor instances
    defaultOptions: {
      width:        500, // width not including margins, borders or padding
      height:       250, // height not including margins, borders or padding
      resizable:    false, // dynamicaly resizable
      controls:     // controls to add to the toolbar
                    "bold italic underline strikethrough subscript superscript | font size " +
                    "style | color highlight removeformat | bullets numbering | outdent " +
                    "indent | alignleft center alignright justify | undo redo | " +
                    "rule image link unlink | print source",
      colors:       // colors in the color popup
                    "FFF FCC FC9 FF9 FFC 9F9 9FF CFF CCF FCF " +
                    "CCC F66 F96 FF6 FF3 6F9 3FF 6FF 99F F9F " +
                    "BBB F00 F90 FC6 FF0 3F3 6CC 3CF 66C C6C " +
                    "999 C00 F60 FC3 FC0 3C0 0CC 36F 63F C3C " +
                    "666 900 C60 C93 990 090 399 33F 60C 939 " +
                    "333 600 930 963 660 060 366 009 339 636 " +
                    "000 300 630 633 330 030 033 006 309 303",    
      fonts:        // font names in the font popup
                    "Arial,Arial Black,Comic Sans MS,Courier New,Narrow,Garamond," +
                    "Georgia,Impact,Sans Serif,Serif,Tahoma,Trebuchet MS,Verdana",
      sizes:        // sizes in the font size popup
                    "1,2,3,4,5,6,7",
      styles:       // styles in the style popup
                    [[cli18n.paragraph, "<p>"], [cli18n.header+" 1", "<h1>"], [cli18n.header+" 2", "<h2>"],
                    [cli18n.header+" 3", "<h3>"],  [cli18n.header+" 4","<h4>"],  [cli18n.header+" 5","<h5>"],
                    [cli18n.header+" 6","<h6>"]],
      useCSS:       false, // use CSS to style HTML when possible (not supported in ie)
      docType:      // Document type contained within the editor
                    '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
      docCSSFile:   // CSS file used to style the document contained within the editor
                    "", 
      plugins:      []
    },

    // Define all usable toolbar buttons - the init string property is 
    //   expanded during initialization back into the buttons object and 
    //   seperate object properties are created for each button.
    //   e.g. buttons.size.title = "Font Size"
    buttons: {
      // name,title,command,popupName (""=use name)
      init:
      "bold,"+cli18n.bold+",|" +
      "italic,"+cli18n.italic+",|" +
      "underline,"+cli18n.underline+",|" +
      "strikethrough,"+cli18n.strikethrough+",|" +
      "subscript,"+cli18n.subscript+",|" +
      "superscript,"+cli18n.superscript+",|" +
      "font,"+cli18n.font+",fontname,|" +
      "size,"+cli18n.size+",fontsize,|" +
      "style,"+cli18n.style+",formatblock,|" +
      "color,"+cli18n.color+",forecolor,|" +
      "highlight,"+cli18n.highlight+",hilitecolor,color|" +
      "removeformat,"+cli18n.removeformat+",|" +
      "bullets,"+cli18n.bullets+",insertunorderedlist|" +
      "numbering,"+cli18n.numbering+",insertorderedlist|" +
      "outdent,"+cli18n.outdent+",|" +
      "indent,"+cli18n.indent+",|" +
      "alignleft,"+cli18n.alignleft+",justifyleft|" +
      "center,"+cli18n.center+",justifycenter|" +
      "alignright,"+cli18n.alignright+",justifyright|" +
      "justify,"+cli18n.justify+",justifyfull|" +
      "undo,"+cli18n.undo+",|" +
      "redo,"+cli18n.redo+",|" +
      "rule,"+cli18n.rule+",inserthorizontalrule|" +
      "image,"+cli18n.image+",insertimage,url|" +
      "link,"+cli18n.link+",createlink,url|" +
      "unlink,"+cli18n.unlink+",|" +
      "print,"+cli18n.print+",|" +
      "source,"+cli18n.source+""
    },

    // imagesPath - returns the path to the images folder
    imagesPath: function() { return imagesPath(); }

  };

  // cleditor - creates a new editor for each of the matched textareas
  $.fn.cleditor = function(options) {

    // Create a new jQuery object to hold the results
    var $result = $([]);

    // Loop through all matching textareas and create the editors
    this.each(function(idx, elem) {
      if (elem.tagName == "TEXTAREA") {
        var data = $.data(elem, CLEDITOR);
        if (!data) data = new cleditor(elem, options);
        $result = $result.add(data);
      }
    });

    // return the new jQuery object
    return $result;

  };
    
  //==================
  // Private Variables
  //==================

  var

  // Misc constants
  BACKGROUND_COLOR = "backgroundColor",
  BUTTON           = "button",
  BUTTON_NAME      = "buttonName",
  CHANGE           = "change",
  CLEDITOR         = "cleditor",
  CLICK            = "click",
  DISABLED         = "disabled",
  DIV_TAG          = "<div>",
  TRANSPARENT      = "transparent",
  UNSELECTABLE     = "unselectable",

  // Class name constants
  MAIN_CLASS       = "cleditorMain",    // main containing div
  TOOLBAR_CLASS    = "cleditorToolbar", // toolbar div inside main div
  GROUP_CLASS      = "cleditorGroup",   // group divs inside the toolbar div
  BUTTON_CLASS     = "cleditorButton",  // button divs inside group div
  DISABLED_CLASS   = "cleditorDisabled",// disabled button divs
  DIVIDER_CLASS    = "cleditorDivider", // divider divs inside group div
  POPUP_CLASS      = "cleditorPopup",   // popup divs inside body
  LIST_CLASS       = "cleditorList",    // list popup divs inside body
  COLOR_CLASS      = "cleditorColor",   // color popup div inside body
  PROMPT_CLASS     = "cleditorPrompt",  // prompt popup divs inside body
  MSG_CLASS        = "cleditorMsg",     // message popup div inside body

  // Test for ie
  ie = $.browser.msie,
  ie6 = /msie\s6/i.test(navigator.userAgent),

  // Test for iPhone/iTouch/iPad
  iOS = /iphone|ipad|ipod/i.test(navigator.userAgent),

  // Popups are created once as needed and shared by all editor instances
  popups = {},

  // Used to prevent the document click event from being bound more than once
  documentClickAssigned,

  // Local copy of the buttons object
  buttons = $.cleditor.buttons;

  //===============
  // Initialization
  //===============

  // Expand the buttons.init string back into the buttons object
  //   and create seperate object properties for each button.
  //   e.g. buttons.size.title = "Font Size"
  $.each(buttons.init.split("|"), function(idx, button) {
    var items = button.split(","), name = items[0];
    buttons[name] = {
      stripIndex: idx,
      name: name,
      title: items[1] === "" ? name.charAt(0).toUpperCase() + name.substr(1) : items[1],
      command: items[2] === "" ? name : items[2],
      popupName: items[3] === "" ? name : items[3]
    };
  });
  delete buttons.init;

  //============
  // Constructor
  //============

  // cleditor - creates a new editor for the passed in textarea element
  cleditor = function(area, options) {

    var editor = this;

    // Get the defaults and override with options
    editor.options = options = $.extend({}, $.cleditor.defaultOptions, options);

    // Hide the textarea and associate it with this editor
    var $area = editor.$area = $(area)
      .hide()
      .data(CLEDITOR, editor)
      .blur(function() {
        // Update the iframe when the textarea loses focus
        editor.updateFrame(true);
      });

    // Create the main container and append the textarea
    var $main = editor.$main = $(DIV_TAG)
      .addClass(MAIN_CLASS)
      .width(options.width)
      .height(options.height);

    // Create the toolbar
    var $toolbar = editor.$toolbar = $(DIV_TAG)
      .addClass(TOOLBAR_CLASS)
      .appendTo($main);

    // Add the first group to the toolbar
    var $group = $(DIV_TAG)
      .addClass(GROUP_CLASS)
      .appendTo($toolbar);
    
    // Add the buttons to the toolbar
    $.each(options.controls.split(" "), function(idx, buttonName) {
      if (buttonName === "") return true;

      // Divider
      if (buttonName == "|") {

        // Add a new divider to the group
        var $div = $(DIV_TAG)
          .addClass(DIVIDER_CLASS)
          .appendTo($group);

        // Create a new group
        $group = $(DIV_TAG)
          .addClass(GROUP_CLASS)
          .appendTo($toolbar);

      }

      // Button
      else {
        
        // Get the button definition
        var button = buttons[buttonName];

        // Add a new button to the group
        var $buttonDiv = $(DIV_TAG)
          .data(BUTTON_NAME, button.name)
          .addClass(BUTTON_CLASS)
          .attr("title", button.title)
          .bind(CLICK, $.proxy(buttonClick, editor))
          .appendTo($group)
          .hover(hoverEnter, hoverLeave);

        // Prepare the button image
        var map = {};
        if (button.css) map = button.css;
        else if (button.image) map.backgroundImage = imageUrl(button.image);
        if (button.stripIndex) map.backgroundPosition = button.stripIndex * -24;
        $buttonDiv.css(map);

        // Add the unselectable attribute for ie
        if (ie)
          $buttonDiv.attr(UNSELECTABLE, "on");

        // Create the popup
        if (button.popupName)
          createPopup(button.popupName, options, button.popupClass,
            button.popupContent, button.popupHover);
        
      }

    });

    // Add the main div to the DOM and append the textarea
    $main.insertBefore($area)
      .append($area);

    // Bind the window resize event when the width or height is auto or %
    if (/auto|%/.test("" + options.width + options.height))
      $(window).resize(function() {editor.refresh();});

    // Create the iframe and resize the controls
    this.refresh();

  };

  //===============
  // Public Methods
  //===============

  cleditor.prototype = {

      // Clears the contents of the editor
      clear:
          function() {
              this.$area.val("");
              this.updateFrame();
          },

      // Enables or disables the editor
      disable:
          function(disabled) {

              // Update the textarea and save the state
              if (disabled) {
                  this.$area.attr(DISABLED, DISABLED);
                  this.disabled = true;
              }
              else {
                  this.$area.removeAttr(DISABLED);
                  delete this.disabled;
              }

              // Switch the iframe into design mode.
              // ie6 does not support designMode.
              // ie7 & ie8 do not properly support designMode="off".
              try {
                  if (ie) this.doc.body.contentEditable = !disabled;
                  else this.doc.designMode = !disabled ? "on" : "off";
              }
              // Firefox 1.5 throws an exception that can be ignored
              // when toggling designMode from off to on.
              catch (err) {}

              // Enable or disable the toolbar buttons
              refreshButtons(this);
          },

      // Executes a designMode command
      execCommand:
          function(command, value, useCSS, button) {

              // Restore the current ie selection
              restoreRange(this);

              // Set the styling method
              if (!ie) {
                  if (useCSS === undefined || useCSS === null)
                      useCSS = this.options.useCSS;
                  this.doc.execCommand("styleWithCSS", 0, useCSS.toString());
              }

              // Execute the command and check for error
              var success = true, description;
              if (ie && command.toLowerCase() == "inserthtml")
                  this.getRange().pasteHTML(value);
              else {
                  try { success = this.doc.execCommand(command, 0, value || null); }
                  catch (err) { description = err.description; success = false; }
                  if (!success) {
                      this.showMessage(
                              (description ? description : cli18n.execError1 + command + cli18n.execError2), button);
                  }
              }

              // Enable the buttons
              refreshButtons(this);
              return success;

          },

      // Sets focus to either the textarea or iframe
      focus:
          function() {
              setTimeout($.proxy(function() {
                  if (this.sourceMode()) this.$area.focus();
                  else this.$frame[0].contentWindow.focus();
                  refreshButtons(this);
              }, this), 0);
          },

      // Hides all popups
      hidePopups:
          function() {
              $.each(popups, function(idx, popup) {
                  $(popup)
                  .hide()
                  .unbind(CLICK)
                  .removeData(BUTTON);
              });
          },

      // Returns true if the textarea is showing
      sourceMode:
          function() {
              return this.$area.is(":visible");
          },


      // Creates the iframe and resizes the controls
      refresh:
          function() {

              var $main = this.$main,
              options = this.options;

              // Remove the old iframe
              if (this.$frame) 
                  this.$frame.remove();

              // Create a new iframe
              var $frame = this.$frame = $('<iframe frameborder="0" src="javascript:true;">')
                  .hide()
                  .appendTo($main);

              // Load the iframe document content
              var contentWindow = $frame[0].contentWindow,
                  doc = this.doc = contentWindow.document,
                  $doc = $(doc);

              doc.open();
              doc.write(
                      options.docType +
                      '<html>' +
                      ((options.docCSSFile === '') ?
                       '' : '<head><link rel="stylesheet" type="text/css" href="' + options.docCSSFile + '" /></head>') +
                      '<body></body></html>'
                      );
              doc.close();

              // Work around for bug in IE which causes the editor to lose
              // focus when clicking below the end of the document.
              if (ie)
                  $doc.click(function() {this.focus();});

              // Load the content
              this.updateFrame();

              // Bind the ie specific iframe event handlers
              if (ie) {

                  // Save the current user selection. This code is needed since IE will
                  // reset the selection just after the beforedeactivate event and just
                  // before the beforeactivate event.
                  $doc.bind("beforedeactivate beforeactivate selectionchange keypress", function(e) {

                      // Flag the editor as inactive
                      if (e.type == "beforedeactivate")
                      this.inactive = true;

                  // Get rid of the bogus selection and flag the editor as active
                      else if (e.type == "beforeactivate") {
                          if (!this.inactive && this.range && this.range.length > 1)
                      this.range.shift();
                  delete this.inactive;
                      }

                  // Save the selection when the editor is active
                      else if (!this.inactive) {
                          if (!this.range) 
                      this.range = [];
                  this.range.unshift(this.getRange());

                  // We only need the last 2 selections
                  while (this.range.length > 2)
                      this.range.pop();
                      }

                  });

                  // Restore the text range when the iframe gains focus
                  $frame.focus(function() {
                      restoreRange(this);
                  });

              }

              // Update the textarea when the iframe loses focus
              ($.browser.mozilla ? $doc : $(contentWindow)).blur(
                      $.proxy(function() {
                          this.updateTextArea(true);
                      }, this));

              // Enable the toolbar buttons as the user types or clicks
              $doc.click(this.hidePopups)
                  .bind("keyup mouseup", $.proxy(
                              function() {
                                  refreshButtons(this);
                              }, this));

              // Show the textarea for iPhone/iTouch/iPad or
              // the iframe when design mode is supported.
              if (iOS) this.$area.show();
              else if (!this.sourceMode()) $frame.show();

              // Initialization of plugins.
              var editor = this;
              $.each(options.plugins,
                      function(idx, setupPlugin) {
                          setupPlugin(editor);
                      });

              // Wait for the layout to finish.
              $(document).ready($.proxy(function() {

                  var $toolbar = this.$toolbar, $group = $toolbar.children("div:last"), wid = $main.width();

                  // Resize the toolbar
                  var hgt = $group.offset().top + $group.outerHeight() - $toolbar.offset().top + 1;
                  $toolbar.height(hgt);

                  // Resize the iframe
                  hgt = $main.height() - hgt;
                  $frame.width(wid).height(hgt);

                  // Resize the textarea. IE6 textareas have a 1px top
                  // & bottom margin that cannot be removed using css.
                  this.$area.width(wid).height(ie6 ? hgt - 2 : hgt);

                  // Switch the iframe into design mode if enabled
                  this.disable(this.disabled);

                  // Enable or disable the toolbar buttons
                  refreshButtons(this);
              }, this));

          },

      // Selects all the text in either the textarea or iframe
      select:
          function() {
              setTimeout(function() {
                  if (sourceMode(this)) this.$area.select();
                  else this.execCommand("selectall");
              }, 0);
          },

      // Returns the current HTML selection or and empty string
      selectedHTML:
          function() {
              restoreRange(this);
              var range = this.getRange();
              if (ie)
                  return range.htmlText;
              var layer = $("<layer>")[0];
              layer.appendChild(range.cloneContents());
              var html = layer.innerHTML;
              layer = null;
              return html;
          },

      // Returns the current text selection or and empty string
      selectedText:
          function() {
              restoreRange(this);
              if (ie) return this.getRange().text;
              return this.getSelection().toString();
          },

      // Alert replacement
      showMessage:
          function(message, button) {
              var popup = createPopup("msg", this.options, MSG_CLASS);
              popup.innerHTML = message;
              showPopup(this, popup, button);
          },

      // Updates the iframe with the textarea contents
      updateFrame:
          function(checkForChange) {

              var code = this.$area.val(),
              options = this.options,
              updateFrameCallback = options.updateFrame,
              $body = $(this.doc.body);

              // Check for textarea change to avoid unnecessary firing
              // of potentially heavy updateFrame callbacks.
              if (updateFrameCallback) {
                  var sum = checksum(code);
                  if (checkForChange && this.areaChecksum == sum)
                      return;
                  this.areaChecksum = sum;
              }

              // Convert the textarea source code into iframe html
              var html = updateFrameCallback ? updateFrameCallback(code) : code;

              // Prevent script injection attacks by html encoding script tags
              html = html.replace(/<(?=\/?script)/ig, "&lt;");

              // Update the iframe checksum
              if (options.updateTextArea)
                  this.frameChecksum = checksum(html);

              // Update the iframe and trigger the change event
              if (html != $body.html()) {
                  $body.html(html);
                  $(this).triggerHandler(CHANGE);
              }

          },

      // Updates the textarea with the iframe contents
      updateTextArea:
          function(checkForChange) {

              var html = $(this.doc.body).html(),
              options = this.options,
              updateTextAreaCallback = options.updateTextArea,
              $area = this.$area;

              // Check for iframe change to avoid unnecessary firing
              // of potentially heavy updateTextArea callbacks.
              if (updateTextAreaCallback) {
                  var sum = checksum(html);
                  if (checkForChange && this.frameChecksum == sum)
                      return;
                  this.frameChecksum = sum;
              }

              // Convert the iframe html into textarea source code
              var code = updateTextAreaCallback ? updateTextAreaCallback(html) : html;

              // Update the textarea checksum
              if (options.updateFrame)
                  this.areaChecksum = checksum(code);

              // Update the textarea and trigger the change event
              if (code != $area.val()) {
                  $area.val(code);
                  $(this).triggerHandler(CHANGE);
              }

          },

      change:
          function(handler) {
              var $this = $(this);
              return handler ? $this.bind(CHANGE, handler) : $this.trigger(CHANGE);
          },

      // Gets the current text range object
      getRange:
          function() {
              if (ie) return this.getSelection().createRange();
              return this.getSelection().getRangeAt(0);
          },

      // Gets the current text range object
      getSelection:
          function() {
              if (ie) return this.doc.selection;
              return this.$frame[0].contentWindow.getSelection();
          },



  };

  //===============
  // Event Handlers
  //===============

  // buttonClick - click event handler for toolbar buttons
  function buttonClick(e) {

    var editor = this,
        buttonDiv = e.target,
        buttonName = $.data(buttonDiv, BUTTON_NAME),
        button = buttons[buttonName],
        popupName = button.popupName,
        popup = popups[popupName];

    // Check if disabled
    if (editor.disabled || $(buttonDiv).attr(DISABLED) == DISABLED)
      return;

    // Fire the buttonClick event
    var data = {
      editor: editor,
      button: buttonDiv,
      buttonName: buttonName,
      popup: popup,
      popupName: popupName,
      command: button.command,
      useCSS: editor.options.useCSS
    };

    if (button.buttonClick && button.buttonClick(e, data) === false)
      return false;

    // Toggle source
    if (buttonName == "source") {

      // Show the iframe
      if (editor.sourceMode()) {
        delete editor.range;
        editor.$area.hide();
        editor.$frame.show();
        buttonDiv.title = button.title;
      }

      // Show the textarea
      else {
        editor.$frame.hide();
        editor.$area.show();
        buttonDiv.title = cli18n.showRichText;
      }

      // Enable or disable the toolbar buttons
      // IE requires the timeout
      setTimeout(function() {refreshButtons(editor);}, 100);

    }

    // Check for rich text mode
    else if (!this.sourceMode()) {

      // Handle popups
      if (popupName) {
        var $popup = $(popup);

        // URL
        if (popupName == "url") {

          // Check for selection before showing the link url popup
          if (buttonName == "link" && editor.selectedText() === "") {
            editor.showMessage(cli18n.selectionRequired, buttonDiv);
            return false;
          }

          // Wire up the submit button click event handler
          $popup.children(":button")
            .unbind(CLICK)
            .bind(CLICK, function() {

              // Insert the image or link if a url was entered
              var $text = $popup.find(":text"),
                url = $.trim($text.val());
              if (url !== "")
                editor.execCommand(data.command, url, null, data.button);

              // Reset the text, hide the popup and set focus
              $text.val("http://");
              editor.hidePopups();
              editor.focus();

            });

        }

        // Show the popup if not already showing for this button
        if (buttonDiv !== $.data(popup, BUTTON)) {
          showPopup(editor, popup, buttonDiv);
          return false; // stop propagination to document click
        }

        // propaginate to documnt click
        return;

      }

      // Print
      else if (buttonName == "print")
        editor.$frame[0].contentWindow.print();

      // All other buttons
      else if (!editor.execCommand(data.command, data.value, data.useCSS, buttonDiv))
        return false;

    }

    // Focus the editor
    editor.focus();

  }

  // hoverEnter - mouseenter event handler for buttons and popup items
  function hoverEnter(e) {
    var $div = $(e.target).closest("div");
    $div.css(BACKGROUND_COLOR, $div.data(BUTTON_NAME) ? "#FFF" : "#FFC");
  }

  // hoverLeave - mouseleave event handler for buttons and popup items
  function hoverLeave(e) {
    $(e.target).closest("div").css(BACKGROUND_COLOR, "transparent");
  }

  // popupClick - click event handler for popup items
  function popupClick(e) {

    var editor = this,
        popup = e.data.popup,
        target = e.target;

    // Check for message and prompt popups
    if (popup === popups.msg || $(popup).hasClass(PROMPT_CLASS))
      return;

    // Get the button info
    var buttonDiv = $.data(popup, BUTTON),
        buttonName = $.data(buttonDiv, BUTTON_NAME),
        button = buttons[buttonName],
        command = button.command,
        value,
        useCSS = editor.options.useCSS;

    // Get the command value
    if (buttonName == "font")
      // Opera returns the fontfamily wrapped in quotes
      value = target.style.fontFamily.replace(/"/g, "");
    else if (buttonName == "size") {
      if (target.tagName == "DIV")
        target = target.children[0];
      value = target.innerHTML;
    }
    else if (buttonName == "style")
      value = "<" + target.tagName + ">";
    else if (buttonName == "color")
      value = hex(target.style.backgroundColor);
    else if (buttonName == "highlight") {
      value = hex(target.style.backgroundColor);
      if (ie) command = 'backcolor';
      else useCSS = true;
    }

    // Fire the popupClick event
    var data = {
      editor: editor,
      button: buttonDiv,
      buttonName: buttonName,
      popup: popup,
      popupName: button.popupName,
      command: command,
      value: value,
      useCSS: useCSS
    };

    if (button.popupClick && button.popupClick(e, data) === false)
      return;

    // Execute the command
    if (data.command && !editor.execCommand(data.command, data.value, data.useCSS, buttonDiv))
      return false;

    // Hide the popup and focus the editor
    editor.hidePopups();
    editor.focus();

  }

  //==================
  // Private Functions
  //==================

  // checksum - returns a checksum using the Adler-32 method
  function checksum(text)
  {
    var a = 1, b = 0;
    for (var index = 0; index < text.length; ++index) {
      a = (a + text.charCodeAt(index)) % 65521;
      b = (b + a) % 65521;
    }
    return (b << 16) | a;
  }

  // createPopup - creates a popup and adds it to the body
  function createPopup(popupName, options, popupTypeClass, popupContent, popupHover) {

    // Check if popup already exists
    if (popups[popupName])
      return popups[popupName];

    // Create the popup
    var $popup = $(DIV_TAG)
      .hide()
      .addClass(POPUP_CLASS)
      .appendTo("body");

    // Add the content

    // Custom popup
    if (popupContent)
      $popup.html(popupContent);

    // Color
    else if (popupName == "color") {
      var colors = options.colors.split(" ");
      if (colors.length < 10)
        $popup.width("auto");
      $.each(colors, function(idx, color) {
        $(DIV_TAG).appendTo($popup)
          .css(BACKGROUND_COLOR, "#" + color);
      });
      popupTypeClass = COLOR_CLASS;
    }

    // Font
    else if (popupName == "font")
      $.each(options.fonts.split(","), function(idx, font) {
        $(DIV_TAG).appendTo($popup)
          .css("fontFamily", font)
          .html(font);
      });

    // Size
    else if (popupName == "size")
      $.each(options.sizes.split(","), function(idx, size) {
        $(DIV_TAG).appendTo($popup)
          .html("<font size=" + size + ">" + size + "</font>");
      });

    // Style
    else if (popupName == "style")
      $.each(options.styles, function(idx, style) {
        $(DIV_TAG).appendTo($popup)
          .html(style[1] + style[0] + style[1].replace("<", "</"));
      });

    // URL
    else if (popupName == "url") {
      $popup.html(cli18n.enterUrl+':<br><input type=text value="http://" size=35><br><input type=button value="'+cli18n.submit+'">');
      popupTypeClass = PROMPT_CLASS;
    }

    // Add the popup type class name
    if (!popupTypeClass && !popupContent)
      popupTypeClass = LIST_CLASS;
    $popup.addClass(popupTypeClass);

    // Add the unselectable attribute to all items
    if (ie) {
      $popup.attr(UNSELECTABLE, "on")
        .find("div,font,p,h1,h2,h3,h4,h5,h6")
        .attr(UNSELECTABLE, "on");
    }

    // Add the hover effect to all items
    if ($popup.hasClass(LIST_CLASS) || popupHover === true)
      $popup.children().hover(hoverEnter, hoverLeave);

    // Add the popup to the array and return it
    popups[popupName] = $popup[0];
    return $popup[0];

  }

  // Returns the hex value for the passed in string.
  //   hex("rgb(255, 0, 0)"); // #FF0000
  //   hex("#FF0000"); // #FF0000
  //   hex("#F00"); // #FF0000
  function hex(s) {
    var m = /rgba?\((\d+), (\d+), (\d+)/.exec(s),
      c = s.split("");
    if (m) {
      s = ( m[1] << 16 | m[2] << 8 | m[3] ).toString(16);
      while (s.length < 6)
        s = "0" + s;
    }
    return "#" + (s.length == 6 ? s : c[1] + c[1] + c[2] + c[2] + c[3] + c[3]);
  }

  // imagesPath - returns the path to the images folder
  function imagesPath() {
    var cssFile = "jquery.cleditor.css",
        href = $("link[href$='" + cssFile +"']").attr("href");
    return href.substr(0, href.length - cssFile.length) + "images/";
  }

  // imageUrl - Returns the css url string for a filemane
  function imageUrl(filename) {
    return "url(" + imagesPath() + filename + ")";
  }

  // refreshButtons - enables or disables buttons based on availability
  function refreshButtons(editor) {

    // Webkit requires focus before queryCommandEnabled will return anything but false
    if (!iOS && $.browser.webkit && !editor.focused) {
      editor.$frame[0].contentWindow.focus();
      window.focus();
      editor.focused = true;
    }

    // Get the object used for checking queryCommandEnabled
    var queryObj = editor.doc;
    if (ie) queryObj = editor.getRange();

    // Loop through each button
    var inSourceMode = editor.sourceMode();
    $.each(editor.$toolbar.find("." + BUTTON_CLASS), function(idx, elem) {

      var $elem = $(elem),
        button = $.cleditor.buttons[$.data(elem, BUTTON_NAME)],
        command = button.command,
        enabled = true;

      // Determine the state
      if (editor.disabled)
        enabled = false;
      else if (button.getEnabled) {
        var data = {
          editor: editor,
          button: elem,
          buttonName: button.name,
          popup: popups[button.popupName],
          popupName: button.popupName,
          command: button.command,
          useCSS: editor.options.useCSS
        };
        enabled = button.getEnabled(data);
        if (enabled === undefined)
          enabled = true;
      }
      else if (((inSourceMode || iOS) && button.name != "source") ||
      (ie && (command == "undo" || command == "redo")))
        enabled = false;
      else if (command && command != "print") {
        if (ie && command == "hilitecolor")
          command = "backcolor";
        // IE does not support inserthtml, so it's always enabled
        if (!ie || command != "inserthtml") {
          try {enabled = queryObj.queryCommandEnabled(command);}
          catch (err) {enabled = false;}
        }
      }

      // Enable or disable the button
      if (enabled) {
        $elem.removeClass(DISABLED_CLASS);
        $elem.removeAttr(DISABLED);
      }
      else {
        $elem.addClass(DISABLED_CLASS);
        $elem.attr(DISABLED, DISABLED);
      }

    });
  }

  // restoreRange - restores the current ie selection
  function restoreRange(editor) {
    if (ie && editor.range)
      editor.range[0].select();
  }

  // showPopup - shows a popup
  function showPopup(editor, popup, button) {

    var offset, left, top, $popup = $(popup);

    // Determine the popup location
    if (button) {
      var $button = $(button);
      offset = $button.offset();
      left = --offset.left;
      top = offset.top + $button.height();
    }
    else {
      var $toolbar = editor.$toolbar;
      offset = $toolbar.offset();
      left = Math.floor(($toolbar.width() - $popup.width()) / 2) + offset.left;
      top = offset.top + $toolbar.height() - 2;
    }

    // Position and show the popup
    editor.hidePopups();
    $popup.css({left: left, top: top})
      .show();

    // Assign the popup button and click event handler
    if (button) {
      $.data(popup, BUTTON, button);
      $popup.bind(CLICK, {popup: popup}, $.proxy(popupClick, editor));
    }

    // Focus the first input element if any
    setTimeout(function() {
      $popup.find(":text,textarea").eq(0).focus().select();
    }, 100);

  }

})(jQuery);
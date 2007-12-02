/*
 * Package: filearea
 * 
 * Summary:
 * 		The file area widget
 * 
 */
dojo.provide("api.filearea");
dojo.provide("api.filearea._item");
dojo.require("dijit._Widget");
dojo.require("dijit._Templated");
dojo.require("dijit._Container");

dojo.declare(
	"api.filearea",
	[dijit._Widget, dijit._Templated, dijit._Container, dijit._Contained],
{
	path: "/",
	extensions: [],
	iconStyle: "list",
	overflow: "scroll",
	subdirs: true,
	textShadow: false,
	templateString: "<div class='desktopFileArea' dojoAttachEvent='onclick:_onClick' dojoAttachPoint='focusNode,containerNode' style='overflow-x: hidden; overflow-y: ${overflow};'></div></div>",
	refresh: function()
	{
		dojo.forEach(this.getChildren(), dojo.hitch(this, function(item){
			item.destroy();
		}));
		api.fs.ls({
			path: this.path,
			callback: dojo.hitch(this, function(array)
			{
				dojo.forEach(array, dojo.hitch(this, function(item) {
					if(desktop.config.filesystem.hideExt && !item.isDir)
					{
						var p = item.file.lastIndexOf(".");
						item.file = item.file.substring(0, p);
					}
					this.addChild(new api.filearea._item({
						label: item.file,
						iconClass: (item.isDir ? "icon-32-places-folder" : "icon-32-mimetypes-text-x-generic"),
						isDir: item.isDir,
						path: this.path+item.file,
						textshadow: this.textShadow
					}));
				}));
			})
		});
	},
	clearSelection: function()
	{
		dojo.forEach(this.getChildren(), dojo.hitch(this, function(item){
			item.unhighlight();
		}));
	},
	up: function()
	{
		if (this.path != "/") {
			dirs = this.path.split("/");
			if(this.path.charAt(this.path.length-1) == "/") dirs.pop();
			if(this.path.charAt(0) == "/") dirs.shift();
			dirs.pop();
			if(dirs.length == 0) this.path = "/";
			else this.path = "/"+dirs.join("/")+"/";
			this.refresh();
		}
	},
	setExtension: function(type, parameter, extension)
	{
		this.extensions[this.extensions.length] = {type: type, parameter: parameter, extension: extension};
	},
	handleExtension: function(path)
	{
		api.console("filearea: extension handling...");
		for(a=0;a<this.extensions.length;a++) {
			var p = path.lastIndexOf(".");
			p = path.slice(p);
			api.console("extension: "+this.extensions[a].extension+"; p: "+p);
			if(this.extensions[a].extension == p) {
				api.console("filearea: found extension handler, calling it");
				if(this.extensions[a].type == "function") {
					this.extensions[a].parameter(path);
				}
				if(this.extensions[a].type == "application") {
					desktop.app.launch(this.extensions[a].parameter, { path: path });
				}
			}
		}
	},
	setPath: function(path)
	{
		if (this.subdirs) {
			this.path = path;
			this.refresh();
		}
	},
	_onClick: function(e)
	{
		var w = dijit.getEnclosingWidget(e.target);
		if (w.declaredClass == "api.filearea._item") {
			if (dojo.hasClass(e, "desktopFileItemText")) 
				w._onTextClick();
			else 
				w._onIconClick();
		}
		else this.clearSelection();
	},
	onItem: function(path)
	{
		//this is a hook to use when an item is opened
		alert("test")
	}
});

dojo.declare(
	"api.filearea._item",
	[dijit._Widget, dijit._Templated, dijit._Contained],
{
	iconClass: "",
	label: "file",
	highlighted: false,
	isDir: false,
	textShadow: false,
	templateString: "<div class='desktopFileItem' style='float: left; padding: 10px;' dojoAttachPoint='focusNode'><div class='desktopFileItemIcon ${iconClass}'></div><div class='desktopFileItemText' style='padding-left: 2px; padding-right: 2px;' style='text-align: center;'><div class='desktopFileItemTextFront'>${label}</div><div class='desktopFileItemTextBack'>${label}</div></div></div>",
	postCreate: function() {
		if(!this.textshadow)
		{
			dojo.query(".desktopFileItemTextBack", this.domNode).style("display", "none");
			dojo.query(".desktopFileItemTextFront", this.domNode).removeClass("desktopFileItemTextFront");
		}
	},
	onClick: function()
	{
		this.getParent().onItem(this.path);
	},
	_onIconClick: function(e) {
		if(this.highlighted == false)
		{
			this.getParent().clearSelection();
			this.highlight();
		}
		else
		{
			if (this.isDir) {
				this.getParent().setPath(this.label + "/");
			}
			else {
				this.getParent().handleExtension(this.path);
			}
		}
	},
	_onTextClick: function(e) {
		if(this.highlighted == false)
		{
			this._onIconClick();
		}
		else
		{
			api.console("item renaming started");
		}
	},
	highlight: function() {
		dojo.query(".desktopFileItemIcon", this.domNode).style("opacity", "0.8").addClass("desktopFileItemHighlight");
		dojo.query(".desktopFileItemText", this.domNode).style("backgroundColor", desktop.config.wallpaper.color).addClass("desktopFileItemHighlight");
		this.highlighted = true;
	},
	unhighlight: function() {
		dojo.query(".desktopFileItemIcon", this.domNode).style("opacity", "1").removeClass("desktopFileItemHighlight");
		dojo.query(".desktopFileItemText", this.domNode).style("backgroundColor", "transparent").removeClass("desktopFileItemHighlight");
		this.highlighted = false;
	}
});
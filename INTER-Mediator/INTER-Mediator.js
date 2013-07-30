var INTERMediator_DBAdapter;
INTERMediator_DBAdapter = {
	generate_authParams: function () {
		var b = "",
			c, a;
		if (INTERMediatorOnPage.authUser.length > 0) {
			b = "&clientid=" + encodeURIComponent(INTERMediatorOnPage.clientId) + "&authuser=" + encodeURIComponent(INTERMediatorOnPage.authUser);
			if (INTERMediatorOnPage.isNativeAuth) {
				b += "&response=" + encodeURIComponent(INTERMediatorOnPage.publickey.biEncryptedString(INTERMediatorOnPage.authHashedPassword + "\n" + INTERMediatorOnPage.authChallenge))
			} else {
				if (INTERMediatorOnPage.authHashedPassword && INTERMediatorOnPage.authChallenge) {
					c = new jsSHA(INTERMediatorOnPage.authHashedPassword, "ASCII");
					a = c.getHMAC(INTERMediatorOnPage.authChallenge, "ASCII", "SHA-256", "HEX");
					b += "&response=" + encodeURIComponent(a)
				} else {
					b += "&response=dummy"
				}
			}
		}
		return b
	},
	store_challenge: function (a) {
		if (a !== null) {
			INTERMediatorOnPage.authChallenge = a.substr(0, 24);
			INTERMediatorOnPage.authUserHexSalt = a.substr(24, 32);
			INTERMediatorOnPage.authUserSalt = String.fromCharCode(parseInt(a.substr(24, 2), 16), parseInt(a.substr(26, 2), 16), parseInt(a.substr(28, 2), 16), parseInt(a.substr(30, 2), 16))
		}
	},
	logging_comAction: function (a, d, c, b) {
		INTERMediator.setDebugMessage(INTERMediatorOnPage.getMessages()[a] + "Accessing:" + decodeURI(d) + ", Parameters:" + decodeURI(c + b))
	},
	logging_comResult: function (c, k, a, d, h, l, b, f, e) {
		var g;
		if (INTERMediator.debugMode > 1) {
			if (c.responseText.length > 1000) {
				g = c.responseText.substr(0, 1000) + " ...[trancated]"
			} else {
				g = c.responseText
			}
			INTERMediator.setDebugMessage("myRequest.responseText=" + g);
			INTERMediator.setDebugMessage("Return: resultCount=" + k + ", dbresult=" + INTERMediatorLib.objectToString(a) + "\nReturn: requireAuth=" + d + ", challenge=" + h + ", clientid=" + l + "\nReturn: newRecordKeyValue=" + b + ", changePasswordResult=" + f + ", mediatoken=" + e)
		}
	},
	server_access: function (accessURL, debugMessageNumber, errorMessageNumber) {
		var newRecordKeyValue = "",
			dbresult = "",
			resultCount = 0,
			challenge = null,
			clientid = null,
			requireAuth = false,
			myRequest = null,
			changePasswordResult = null,
			mediatoken = null,
			appPath, authParams;
		appPath = INTERMediatorOnPage.getEntryPath();
		authParams = INTERMediator_DBAdapter.generate_authParams();
		INTERMediator_DBAdapter.logging_comAction(debugMessageNumber, appPath, accessURL, authParams);
		try {
			myRequest = new XMLHttpRequest();
			myRequest.open("POST", appPath, false, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd);
			myRequest.setRequestHeader("charset", "utf-8");
			myRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			myRequest.send(accessURL + authParams);
			eval(myRequest.responseText);
			INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth, challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken);
			INTERMediator_DBAdapter.store_challenge(challenge);
			if (clientid !== null) {
				INTERMediatorOnPage.clientId = clientid
			}
			if (mediatoken !== null) {
				INTERMediatorOnPage.mediaToken = mediatoken
			}
		} catch (e) {
			INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[errorMessageNumber], [e, myRequest.responseText]))
		}
		if (requireAuth) {
			INTERMediator.setDebugMessage("Authentication Required, user/password panel should be show.");
			INTERMediatorOnPage.authHashedPassword = null;
			throw "_im_requath_request_"
		}
		if (!accessURL.match(/access=challenge/)) {
			INTERMediatorOnPage.authCount = 0
		}
		INTERMediatorOnPage.storeCredencialsToCookie();
		return {
			dbresult: dbresult,
			resultCount: resultCount,
			newRecordKeyValue: newRecordKeyValue,
			newPasswordResult: changePasswordResult
		}
	},
	changePassowrd: function (d, a, c) {
		INTERMediatorOnPage.authUser = d;
		if (d != "" && (INTERMediatorOnPage.authChallenge == null || INTERMediatorOnPage.authChallenge.length < 24)) {
			INTERMediatorOnPage.authHashedPassword = "need-hash-pls";
			challengeResult = INTERMediator_DBAdapter.getChallenge();
			if (!challengeResult) {
				INTERMediator.flushMessage();
				return false
			}
		}
		INTERMediatorOnPage.authHashedPassword = SHA1(a + INTERMediatorOnPage.authUserSalt) + INTERMediatorOnPage.authUserHexSalt;
		params = "access=changepassword&newpass=" + INTERMediatorLib.generatePasswordHash(c);
		try {
			result = INTERMediator_DBAdapter.server_access(params, 1029, 1030)
		} catch (b) {
			return false
		}
		return (result.newPasswordResult && result.newPasswordResult === true)
	},
	getChallenge: function () {
		try {
			this.server_access("access=challenge", 1027, 1028)
		} catch (a) {
			if (a == "_im_requath_request_") {
				throw a
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-19" + a.message)
			}
		}
		if (INTERMediatorOnPage.authChallenge == null) {
			return false
		}
		return true
	},
	uploadFile: function (parameters, uploadingFile, doItOnFinish) {
		var newRecordKeyValue = "",
			dbresult = "",
			resultCount = 0,
			challenge = null,
			clientid = null,
			requireAuth = false,
			myRequest = null,
			changePasswordResult = null,
			mediatoken = null,
			appPath, authParams, accessURL;
		appPath = INTERMediatorOnPage.getEntryPath();
		authParams = INTERMediator_DBAdapter.generate_authParams();
		accessURL = "access=uploadfile" + parameters;
		INTERMediator_DBAdapter.logging_comAction(1031, appPath, accessURL, authParams);
		try {
			myRequest = new XMLHttpRequest();
			myRequest.open("POST", appPath, true, INTERMediatorOnPage.httpuser, INTERMediatorOnPage.httppasswd);
			myRequest.setRequestHeader("charset", "utf-8");
			var params = (accessURL + authParams).split("&");
			var fd = new FormData();
			for (var i = 0; i < params.length; i++) {
				var valueset = params[i].split("=");
				fd.append(valueset[0], decodeURIComponent(valueset[1]))
			}
			fd.append("_im_uploadfile", uploadingFile.content);
			myRequest.onreadystatechange = function () {
				switch (myRequest.readyState) {
				case 3:
					break;
				case 4:
					eval(myRequest.responseText);
					INTERMediator_DBAdapter.logging_comResult(myRequest, resultCount, dbresult, requireAuth, challenge, clientid, newRecordKeyValue, changePasswordResult, mediatoken);
					INTERMediator_DBAdapter.store_challenge(challenge);
					if (clientid !== null) {
						INTERMediatorOnPage.clientId = clientid
					}
					if (mediatoken !== null) {
						INTERMediatorOnPage.mediaToken = mediatoken
					}
					if (requireAuth) {
						INTERMediator.setDebugMessage("Authentication Required, user/password panel should be show.");
						INTERMediatorOnPage.authHashedPassword = null;
						throw "_im_requath_request_"
					}
					INTERMediatorOnPage.authCount = 0;
					INTERMediatorOnPage.storeCredencialsToCookie();
					doItOnFinish();
					break
				}
			};
			myRequest.send(fd)
		} catch (e) {
			INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[1032], [e, myRequest.responseText]))
		}
	},
	db_query: function (m) {
		var c = true,
			g, k, f, a, o, h, d, b, n, e;
		if (m.name == null) {
			INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1005));
			c = false
		}
		if (!c) {
			return
		}
		f = "access=select&name=" + encodeURIComponent(m.name);
		f += "&records=" + encodeURIComponent(m.records ? m.records : 10000000);
		if (m.primaryKeyOnly) {
			f += "&pkeyonly=true"
		}
		if (m.fields) {
			for (g = 0; g < m.fields.length; g++) {
				f += "&field_" + g + "=" + encodeURIComponent(m.fields[g])
			}
		}
		a = 0;
		if (m.parentkeyvalue) {
			for (k in m.parentkeyvalue) {
				if (m.parentkeyvalue.hasOwnProperty(k)) {
					f += "&foreign" + a + "field=" + encodeURIComponent(k);
					f += "&foreign" + a + "value=" + encodeURIComponent(m.parentkeyvalue[k]);
					a++
				}
			}
		}
		if (m.useoffset && INTERMediator.startFrom != null) {
			f += "&start=" + encodeURIComponent(INTERMediator.startFrom)
		}
		o = 0;
		if (m.conditions) {
			f += "&condition" + o + "field=" + encodeURIComponent(m.conditions[o]["field"]);
			f += "&condition" + o + "operator=" + encodeURIComponent(m.conditions[o]["operator"]);
			f += "&condition" + o + "value=" + encodeURIComponent(m.conditions[o]["value"]);
			o++
		}
		h = INTERMediator.additionalCondition[m.name];
		if (h) {
			if (h.field) {
				h = [h]
			}
			for (k in h) {
				if (h.hasOwnProperty(k)) {
					f += "&condition" + o + "field=" + encodeURIComponent(h[k]["field"]);
					if (h[k]["operator"] !== undefined) {
						f += "&condition" + o + "operator=" + encodeURIComponent(h[k]["operator"])
					}
					if (h[k]["value"] !== undefined) {
						f += "&condition" + o + "value=" + encodeURIComponent(h[k]["value"])
					}
					o++
				}
			}
		}
		o = 0;
		d = INTERMediator.additionalSortKey[m.name];
		if (d) {
			if (d.field) {
				d = [d]
			}
			for (k in d) {
				f += "&sortkey" + o + "field=" + encodeURIComponent(d[k]["field"]);
				f += "&sortkey" + o + "direction=" + encodeURIComponent(d[k]["direction"]);
				o++
			}
		}
		f += "&randkey" + Math.random();
		b = {};
		try {
			n = this.server_access(f, 1012, 1004);
			b.recordset = n.dbresult;
			b.totalCount = n.resultCount;
			b.count = 0;
			for (e in n.dbresult) {
				b.count++
			}
			if ((m.paging != null) && (m.paging == true)) {
				INTERMediator.pagedSize = m.records;
				INTERMediator.pagedAllCount = n.resultCount
			}
		} catch (l) {
			if (l == "_im_requath_request_") {
				throw l
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-17" + l.message)
			}
			b.recordset = null;
			b.totalCount = 0;
			b.count = 0
		}
		return b
	},
	db_queryWithAuth: function (b, a) {
		var d = false;
		INTERMediatorOnPage.retrieveAuthInfo();
		try {
			d = INTERMediator_DBAdapter.db_query(b)
		} catch (c) {
			if (c == "_im_requath_request_") {
				if (INTERMediatorOnPage.requireAuthentication) {
					if (!INTERMediatorOnPage.isComplementAuthData()) {
						INTERMediatorOnPage.authChallenge = null;
						INTERMediatorOnPage.authHashedPassword = null;
						INTERMediatorOnPage.authenticating(function () {
							d = INTERMediator_DBAdapter.db_queryWithAuth(arg, a)
						});
						return
					}
				}
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-16" + c.message)
			}
		}
		a(d)
	},
	db_update: function (d) {
		var b = true,
			e, c, a;
		if (d.name == null) {
			INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1007));
			b = false
		}
		if (d.dataset == null) {
			INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1011));
			b = false
		}
		if (!b) {
			return
		}
		e = "access=update&name=" + encodeURIComponent(d.name);
		if (d.conditions != null) {
			for (c = 0; c < d.conditions.length; c++) {
				e += "&condition" + c + "field=";
				e += encodeURIComponent(d.conditions[c]["field"]);
				e += "&condition" + c + "operator=";
				e += encodeURIComponent(d.conditions[c]["operator"]);
				if (d.conditions[c]["value"]) {
					e += "&condition" + c + "value=";
					e += encodeURIComponent(d.conditions[c]["value"])
				}
			}
		}
		for (c = 0; c < d.dataset.length; c++) {
			e += "&field_" + c + "=" + encodeURIComponent(d.dataset[c]["field"]);
			e += "&value_" + c + "=" + encodeURIComponent(d.dataset[c]["value"])
		}
		a = this.server_access(e, 1013, 1014);
		return a.dbresult
	},
	db_updateWithAuth: function (b, a) {
		var d = false;
		INTERMediatorOnPage.retrieveAuthInfo();
		try {
			d = INTERMediator_DBAdapter.db_update(b)
		} catch (c) {
			if (c == "_im_requath_request_") {
				if (INTERMediatorOnPage.requireAuthentication) {
					if (!INTERMediatorOnPage.isComplementAuthData()) {
						INTERMediatorOnPage.authChallenge = null;
						INTERMediatorOnPage.authHashedPassword = null;
						INTERMediatorOnPage.authenticating(function () {
							d = INTERMediator_DBAdapter.db_updateWithAuth(arg, a)
						});
						return
					}
				}
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-15" + c.message)
			}
		}
		a(d)
	},
	db_delete: function (c) {
		var b = true,
			e, d, a;
		if (c.name == null) {
			INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1019));
			b = false
		}
		if (c.conditions == null) {
			INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1020));
			b = false
		}
		if (!b) {
			return
		}
		e = "access=delete&name=" + encodeURIComponent(c.name);
		for (d = 0; d < c.conditions.length; d++) {
			e += "&condition" + d + "field=" + encodeURIComponent(c.conditions[d]["field"]);
			e += "&condition" + d + "operator=" + encodeURIComponent(c.conditions[d]["operator"]);
			e += "&condition" + d + "value=" + encodeURIComponent(c.conditions[d]["value"])
		}
		a = this.server_access(e, 1017, 1015);
		return a
	},
	db_deleteWithAuth: function (b, a) {
		var d = false;
		INTERMediatorOnPage.retrieveAuthInfo();
		try {
			d = INTERMediator_DBAdapter.db_delete(b)
		} catch (c) {
			if (c == "_im_requath_request_") {
				if (INTERMediatorOnPage.requireAuthentication) {
					if (!INTERMediatorOnPage.isComplementAuthData()) {
						INTERMediatorOnPage.authChallenge = null;
						INTERMediatorOnPage.authHashedPassword = null;
						INTERMediatorOnPage.authenticating(function () {
							d = INTERMediator_DBAdapter.db_deleteWithAuth(arg, a)
						});
						return
					}
				}
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-14" + c.message)
			}
		}
		a(d)
	},
	db_createRecord: function (e) {
		var h, f, b, d;
		if (e.name == null) {
			INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1021));
			return
		}
		ds = INTERMediatorOnPage.getDataSources();
		var a = null;
		for (key in ds) {
			if (ds[key]["name"] == e.name) {
				a = key;
				break
			}
		}
		if (a === null) {
			alert("no targetname :" + e.name);
			return
		}
		h = "access=new&name=" + encodeURIComponent(e.name);
		var c = 0;
		for (f = 0; f < INTERMediator.additionalFieldValueOnNewRecord.length; f++) {
			var g = INTERMediator.additionalFieldValueOnNewRecord[f];
			h += "&field_" + c + "=" + encodeURIComponent(g.field);
			h += "&value_" + c + "=" + encodeURIComponent(g.value);
			c++
		}
		for (f = 0; f < e.dataset.length; f++) {
			h += "&field_" + c + "=" + encodeURIComponent(e.dataset[f]["field"]);
			h += "&value_" + c + "=" + encodeURIComponent(e.dataset[f]["value"]);
			c++
		}
		b = this.server_access(h, 1018, 1016);
		return b.newRecordKeyValue
	},
	db_createRecordWithAuth: function (b, a) {
		var d = false;
		INTERMediatorOnPage.retrieveAuthInfo();
		try {
			d = INTERMediator_DBAdapter.db_createRecord(b)
		} catch (c) {
			if (c == "_im_requath_request_") {
				if (INTERMediatorOnPage.requireAuthentication) {
					if (!INTERMediatorOnPage.isComplementAuthData()) {
						INTERMediatorOnPage.authChallenge = null;
						INTERMediatorOnPage.authHashedPassword = null;
						INTERMediatorOnPage.authenticating(function () {
							d = INTERMediator_DBAdapter.db_createRecordWithAuth(arg, a)
						});
						return
					}
				}
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-13" + c.message)
			}
		}
		if (a) {
			a(d)
		}
	}
};
var INTERMediatorLib = {
	ignoreEnclosureRepeaterClassName: "_im_ignore_enc_rep",
	rollingRepeaterClassName: "_im_repeater",
	rollingEnclocureClassName: "_im_enclosure",
	generatePasswordHash: function (d) {
		var f, e, c, g, a, b;
		f = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F"];
		e = "";
		c = "";
		for (i = 0; i < 4; i++) {
			g = Math.floor(Math.random() * (128 - 32) + 32);
			a = g & 15;
			b = (g >> 4) & 15;
			e += String.fromCharCode(g);
			c += f[b] + f[a]
		}
		return encodeURIComponent(SHA1(d + e) + c)
	},
	getParentRepeater: function (b) {
		var a = b;
		while (a != null) {
			if (INTERMediatorLib.isRepeater(a, true)) {
				return a
			}
			a = a.parentNode
		}
		return null
	},
	getParentEnclosure: function (b) {
		var a = b;
		while (a != null) {
			if (INTERMediatorLib.isEnclosure(a, true)) {
				return a
			}
			a = a.parentNode
		}
		return null
	},
	isEnclosure: function (e, f) {
		var c, d, b, a;
		if (!e || e.nodeType !== 1) {
			return false
		}
		c = e.tagName;
		d = INTERMediatorLib.getClassAttributeFromNode(e);
		if (d && d.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
			return false
		}
		if ((c === "TBODY") || (c === "UL") || (c === "OL") || (c === "SELECT") || ((c === "DIV" || c === "SPAN") && d && d.indexOf(INTERMediatorLib.rollingEnclocureClassName) >= 0)) {
			if (f) {
				return true
			} else {
				b = e.childNodes;
				for (a = 0; a < b.length; a++) {
					if (INTERMediatorLib.isRepeater(b[a], true)) {
						return true
					}
				}
			}
		}
		return false
	},
	isRepeater: function (f, g) {
		var d, e, c, b;
		if (!f || f.nodeType !== 1) {
			return false
		}
		d = f.tagName;
		e = INTERMediatorLib.getClassAttributeFromNode(f);
		if (e && e.indexOf(INTERMediatorLib.ignoreEnclosureRepeaterClassName) >= 0) {
			return false
		}
		if ((d === "TR") || (d === "LI") || (d === "OPTION") || ((d === "DIV" || d === "SPAN") && e && e.indexOf(INTERMediatorLib.rollingRepeaterClassName) >= 0)) {
			if (g) {
				return true
			} else {
				return a(f)
			}
		}
		return false;

		function a(h) {
			if (INTERMediatorLib.isLinkedElement(h)) {
				return true
			}
			c = h.childNodes;
			for (b = 0; b < c.length; b++) {
				if (c[b].nodeType === 1) {
					if (INTERMediatorLib.isLinkedElement(c[b])) {
						return true
					} else {
						if (a(c[b])) {
							return true
						}
					}
				}
			}
			return false
		}
	},
	isLinkedElement: function (b) {
		var c, a;
		if (b != null) {
			if (INTERMediator.titleAsLinkInfo) {
				if (b.getAttribute("TITLE") != null && b.getAttribute("TITLE").length > 0) {
					return true
				}
			}
			if (INTERMediator.classAsLinkInfo) {
				c = INTERMediatorLib.getClassAttributeFromNode(b);
				if (c != null) {
					a = c.match(/IM\[.*\]/);
					if (a) {
						return true
					}
				}
			}
		}
		return false
	},
	isWidgetElement: function (b) {
		var c, a;
		if (b != null) {
			c = INTERMediatorLib.getClassAttributeFromNode(b);
			if (c != null) {
				a = c.match(/IM_WIDGET\[.*\]/);
				if (a) {
					return true
				}
			}
		}
		return false
	},
	isNamedElement: function (b) {
		var c, a;
		if (b != null) {
			c = b.getAttribute("name");
			if (c != null) {
				a = c.match(/IM\[.*\]/);
				if (a) {
					return true
				}
			}
		}
		return false
	},
	getEnclosureSimple: function (a) {
		if (INTERMediatorLib.isEnclosure(a, true)) {
			return a
		}
		return INTERMediatorLib.getEnclosureSimple(a.parentNode)
	},
	getEnclosure: function (d) {
		var c, b;
		c = d;
		while (c != null) {
			if (INTERMediatorLib.isRepeater(c, true)) {
				b = c
			} else {
				if (a(b, c)) {
					b = null;
					return c
				}
			}
			c = c.parentNode
		}
		return null;

		function a(h, l) {
			var k, g, f, m, e;
			if (!h || !l) {
				return false
			}
			k = h.tagName;
			g = l.tagName;
			if ((k === "TR" && g === "TBODY") || (k === "OPTION" && g === "SELECT") || (k === "LI" && g === "OL") || (k === "LI" && g === "UL")) {
				return true
			}
			if ((g === "DIV" || g === "SPAN")) {
				f = INTERMediatorLib.getClassAttributeFromNode(l);
				if (f && f.indexOf("_im_enclosure") >= 0) {
					m = INTERMediatorLib.getClassAttributeFromNode(h);
					if ((k === "DIV" || k === "SPAN") && m != null && m.indexOf("_im_repeater") >= 0) {
						return true
					} else {
						if (k === "INPUT") {
							e = h.getAttribute("type");
							if (e && ((e.indexOf("radio") >= 0 || e.indexOf("check") >= 0))) {
								return true
							}
						}
					}
				}
			}
			return false
		}
	},
	getLinkedElementInfo: function (g) {
		var b = [],
			c, e, d, a;
		if (INTERMediatorLib.isLinkedElement(g)) {
			if (INTERMediator.titleAsLinkInfo) {
				if (g.getAttribute("TITLE") != null) {
					c = g.getAttribute("TITLE").split(INTERMediator.defDivider);
					for (e = 0; e < c.length; e++) {
						b.push(f(c[e]))
					}
				}
			}
			if (INTERMediator.classAsLinkInfo) {
				d = INTERMediatorLib.getClassAttributeFromNode(g);
				if (d !== null && d.length > 0) {
					a = d.match(/IM\[([^\]]*)\]/);
					c = a[1].split(INTERMediator.defDivider);
					for (e = 0; e < c.length; e++) {
						b.push(f(c[e]))
					}
				}
			}
			return b
		}
		return false;

		function f(k) {
			var h = INTERMediatorOnPage.getOptionsAliases();
			if (h != null && h[k] != null) {
				return h[k]
			}
			return k
		}
	},
	getWidgetInfo: function (f) {
		var b = [],
			c, e, d, a;
		if (INTERMediatorLib.isWidgetElement(f)) {
			d = INTERMediatorLib.getClassAttributeFromNode(f);
			if (d !== null && d.length > 0) {
				a = d.match(/IM_WIDGET\[([^\]]*)\]/);
				c = a[1].split(INTERMediator.defDivider);
				for (e = 0; e < c.length; e++) {
					b.push(c[e])
				}
			}
			return b
		}
		return false
	},
	getNamedInfo: function (f) {
		var b = [],
			c, e, d, a;
		if (INTERMediatorLib.isNamedElement(f)) {
			d = f.getAttribute("name");
			if (d !== null && d.length > 0) {
				a = d.match(/IM\[([^\]]*)\]/);
				c = a[1].split(INTERMediator.defDivider);
				for (e = 0; e < c.length; e++) {
					b.push(c[e])
				}
			}
			return b
		}
		return false
	},
	repeaterTagFromEncTag: function (a) {
		if (a == "TBODY") {
			return "TR"
		} else {
			if (a == "SELECT") {
				return "OPTION"
			} else {
				if (a == "UL") {
					return "LI"
				} else {
					if (a == "OL") {
						return "LI"
					} else {
						if (a == "DIV") {
							return "DIV"
						} else {
							if (a == "SPAN") {
								return "SPAN"
							}
						}
					}
				}
			}
		}
		return null
	},
	getNodeInfoArray: function (b) {
		var c, a;
		c = b.split(INTERMediator.separator);
		a = "", fieldName = "", targetName = "";
		if (c.length == 3) {
			a = c[0];
			fieldName = c[1];
			targetName = c[2]
		} else {
			if (c.length == 2) {
				a = c[0];
				fieldName = c[1]
			} else {
				fieldName = b
			}
		}
		return {
			table: a,
			field: fieldName,
			target: targetName
		}
	},
	getClassAttributeFromNode: function (a) {
		var b = "";
		if (a == null) {
			return ""
		}
		if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
			b = a.getAttribute("className")
		} else {
			b = a.getAttribute("class")
		}
		return b
	},
	setClassAttributeToNode: function (b, a) {
		if (b == null) {
			return
		}
		if (INTERMediator.isIE && INTERMediator.ieVersion < 8) {
			b.setAttribute("className", a)
		} else {
			b.setAttribute("class", a)
		}
	},
	addEvent: function (c, a, b) {
		if (c.addEventListener) {
			c.addEventListener(a, b, false)
		} else {
			if (c.attachEvent) {
				c.attachEvent("on" + a, b)
			}
		}
	},
	toNumber: function (d) {
		var b = "",
			a, e;
		d = (new String(d)).toString();
		for (a = 0; a < d.length; a++) {
			e = d.charAt(a);
			if ((e >= "0" && e <= "9") || e == "-" || e == ".") {
				b += e
			}
		}
		return parseFloat(b)
	},
	RoundHalfToEven: function (a, b) {
		return a
	},
	numberFormat: function (g, k) {
		var c, h, b, d, a, e;
		c = new Array();
		h = INTERMediatorLib.toNumber(g);
		b = "";
		if (h < 0) {
			b = "-";
			h = -h
		}
		d = h - Math.floor(h);
		for (h = Math.floor(h); h > 0; h = Math.floor(h / 1000)) {
			if (h >= 1000) {
				c.push(("000" + (h % 1000).toString()).substr(-3))
			} else {
				c.push(h)
			}
		}
		a = (k == null) ? 0 : INTERMediatorLib.toNumber(k);
		e = (a == 0) ? "" : new String(Math.floor(d * Math.pow(10, a)));
		while (e.length < a) {
			e = "0" + e
		}
		return b + c.reverse().join(",") + (e == "" ? "" : "." + e)
	},
	objectToString: function (c) {
		var d, b, a;
		if (c === null) {
			return "null"
		}
		if (typeof c == "object") {
			d = "";
			if (c.constractor === Array) {
				for (b = 0; b < c.length; b++) {
					d += INTERMediatorLib.objectToString(c[b]) + ", "
				}
				return "[" + d + "]"
			} else {
				for (a in c) {
					d += "'" + a + "':" + INTERMediatorLib.objectToString(c[a]) + ", "
				}
				return "{" + d + "}"
			}
		} else {
			return "'" + c + "'"
		}
	},
	getTargetTableForRetrieve: function (a) {
		if (a.view != null) {
			return a.view
		}
		return a.name
	},
	getTargetTableForUpdate: function (a) {
		if (a.table != null) {
			return a.table
		}
		return a.name
	},
	getInsertedString: function (b, a) {
		var d, c;
		d = b;
		if (a != null) {
			for (c = 1; c <= a.length; c++) {
				d = d.replace("@" + c + "@", a[c - 1])
			}
		}
		return d
	},
	getInsertedStringFromErrorNumber: function (b, a) {
		var d, c;
		d = INTERMediatorOnPage.getMessages()[b];
		if (a != null) {
			for (c = 1; c <= a.length; c++) {
				d = d.replace("@" + c + "@", a[c - 1])
			}
		}
		return d
	},
	getNamedObject: function (d, c, a) {
		var b;
		for (b in d) {
			if (d[b][c] == a) {
				return d[b]
			}
		}
		return null
	},
	getNamedObjectInObjectArray: function (b, d, a) {
		var c;
		for (c = 0; c < b.length; c++) {
			if (b[c][d] == a) {
				return b[c]
			}
		}
		return null
	},
	getNamedValueInObject: function (c, e, b, f) {
		var a = [],
			d;
		for (d in c) {
			if (c[d][e] == b) {
				a.push(c[d][f])
			}
		}
		if (a.length === 0) {
			return null
		} else {
			if (a.length === 1) {
				return a[0]
			} else {
				return a
			}
		}
	},
	is_array: function (a) {
		return a && typeof a === "object" && typeof a.length === "number" && typeof a.splice === "function" && !(a.propertyIsEnumerable("length"))
	},
	getNamedValuesInObject: function (c, g, e, f, b, h) {
		var a = [],
			d;
		for (d in c) {
			if (c[d][g] == e && c[d][f] == b) {
				a.push(c[d][h])
			}
		}
		if (a.length === 0) {
			return null
		} else {
			if (a.length === 1) {
				return a[0]
			} else {
				return a
			}
		}
	},
	getRecordsetFromFieldValueObject: function (b) {
		var c = {}, a;
		for (a in b) {
			c[b[a]["field"]] = b[a]["value"]
		}
		return c
	},
	getNodePath: function (a) {
		var b = "";
		if (a.tagName == null) {
			return ""
		} else {
			return INTERMediatorLib.getNodePath(a.parentNode) + "/" + a.tagName
		}
	},
	getElementsByClassName: function (e, a) {
		var c = [],
			d = new RegExp(a);
		b(e);
		return c;

		function b(g) {
			if (g.nodeType != 1) {
				return
			}
			if (INTERMediatorLib.getClassAttributeFromNode(g) && INTERMediatorLib.getClassAttributeFromNode(g).match(d)) {
				c.push(g)
			}
			for (var f = 0; f < g.children.length; f++) {
				b(g.children[f])
			}
		}
	}
};
if (!Array.indexOf) {
	Array.prototype.indexOf = function (b) {
		var a;
		for (a = 0; a < this.length; a++) {
			if (this[a] === b) {
				return a
			}
		}
		return -1
	}
}
var INTERMediatorOnPage;
INTERMediatorOnPage = {
	authCountLimit: 4,
	authCount: 0,
	authUser: "",
	authHashedPassword: "",
	authUserSalt: "",
	authUserHexSalt: "",
	authChallenge: "",
	requireAuthentication: false,
	clientId: null,
	authRequiredContext: null,
	authStoring: "cookie",
	authExpired: 3600,
	isOnceAtStarting: true,
	publickey: null,
	isNativeAuth: false,
	httpuser: null,
	httppasswd: null,
	mediaToken: null,
	realm: "",
	dbCache: {},
	isShowChangePassword: true,
	getMessages: function () {
		return null
	},
	isComplementAuthData: function () {
		if (this.authUser != null && this.authUser.length > 0 && this.authHashedPassword != null && this.authHashedPassword.length > 0 && this.authUserSalt != null && this.authUserSalt.length > 0 && this.authChallenge != null && this.authChallenge.length > 0) {
			return true
		}
		return false
	},
	retrieveAuthInfo: function () {
		if (this.requireAuthentication) {
			if (this.isOnceAtStarting) {
				switch (this.authStoring) {
				case "cookie":
				case "cookie-domainwide":
					this.authUser = this.getCookie("_im_username");
					this.authHashedPassword = this.getCookie("_im_credential");
					this.mediaToken = this.getCookie("_im_mediatoken");
					break;
				default:
					this.removeCookie("_im_username");
					this.removeCookie("_im_credential");
					this.removeCookie("_im_mediatoken");
					break
				}
				this.isOnceAtStarting = false
			}
			if (this.authUser.length > 0) {
				if (!INTERMediator_DBAdapter.getChallenge()) {
					INTERMediator.flushMessage()
				}
			}
		}
	},
	logout: function () {
		this.authUser = "";
		this.authHashedPassword = "";
		this.authUserSalt = "";
		this.authChallenge = "";
		this.clientId = "";
		this.removeCookie("_im_username");
		this.removeCookie("_im_credential");
		this.removeCookie("_im_mediatoken")
	},
	storeCredencialsToCookie: function () {
		switch (INTERMediatorOnPage.authStoring) {
		case "cookie":
			INTERMediatorOnPage.setCookie("_im_username", INTERMediatorOnPage.authUser);
			INTERMediatorOnPage.setCookie("_im_credential", INTERMediatorOnPage.authHashedPassword);
			if (INTERMediatorOnPage.mediaToken) {
				INTERMediatorOnPage.setCookie("_im_mediatoken", INTERMediatorOnPage.mediaToken)
			}
			break;
		case "cookie-domainwide":
			INTERMediatorOnPage.setCookieDomainWide("_im_username", INTERMediatorOnPage.authUser);
			INTERMediatorOnPage.setCookieDomainWide("_im_credential", INTERMediatorOnPage.authHashedPassword);
			if (INTERMediatorOnPage.mediaToken) {
				INTERMediatorOnPage.setCookieDomainWide("_im_mediatoken", INTERMediatorOnPage.mediaToken)
			}
			break
		}
	},
	defaultBackgroundImage: "url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAAACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHRJREFUeJzs0bENAEAMAjHWzBC/f5sxkPIurkcmSV65KQcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAL4AaA9oHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOA6wAAAA//8DAF3pMFsPzhYWAAAAAElFTkSuQmCC)",
	defaultBackgroundColor: null,
	loginPanelHTML: null,
	authenticating: function (f) {
		var b, g, o, v, h, e, s, k, t, l, a, p, d, n, c, r, q, u, m;
		if (this.authCount > this.authCountLimit) {
			this.authenticationError();
			this.logout();
			INTERMediator.flushMessage();
			return
		}
		b = document.getElementsByTagName("BODY")[0];
		g = document.createElement("div");
		b.insertBefore(g, b.childNodes[0]);
		g.style.height = "100%";
		g.style.width = "100%";
		if (INTERMediatorOnPage.defaultBackgroundImage) {
			g.style.backgroundImage = INTERMediatorOnPage.defaultBackgroundImage
		}
		if (INTERMediatorOnPage.defaultBackgroundColor) {
			g.style.backgroundColor = INTERMediatorOnPage.defaultBackgroundColor
		}
		g.style.position = "absolute";
		g.style.padding = " 50px 0 0 0";
		g.style.top = "0";
		g.style.left = "0";
		g.style.zIndex = "999998";
		if (INTERMediatorOnPage.loginPanelHTML) {
			g.innerHTML = INTERMediatorOnPage.loginPanelHTML;
			l = document.getElementById("_im_password");
			s = document.getElementById("_im_username");
			d = document.getElementById("_im_authbutton");
			p = document.getElementById("_im_changebutton")
		} else {
			o = document.createElement("div");
			o.style.width = "450px";
			o.style.backgroundColor = "#333333";
			o.style.color = "#DDDDAA";
			o.style.margin = "50px auto 0 auto";
			o.style.padding = "20px";
			o.style.borderRadius = "10px";
			o.style.position = "relative";
			g.appendChild(o);
			if (INTERMediatorOnPage.realm.length > 0) {
				u = document.createElement("DIV");
				u.appendChild(document.createTextNode(INTERMediatorOnPage.realm));
				u.style.textAlign = "left";
				o.appendChild(u);
				a = document.createElement("HR");
				o.appendChild(a)
			}
			v = "110px";
			h = document.createElement("LABEL");
			o.appendChild(h);
			e = document.createElement("div");
			e.style.width = v;
			e.style.textAlign = "right";
			e.style.cssFloat = "left";
			h.appendChild(e);
			e.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2002)));
			s = document.createElement("INPUT");
			s.type = "text";
			s.id = "_im_username";
			s.size = "12";
			h.appendChild(s);
			a = document.createElement("BR");
			a.clear = "all";
			o.appendChild(a);
			k = document.createElement("LABEL");
			o.appendChild(k);
			t = document.createElement("SPAN");
			t.style.minWidth = v;
			t.style.textAlign = "right";
			t.style.cssFloat = "left";
			k.appendChild(t);
			t.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2003)));
			l = document.createElement("INPUT");
			l.type = "password";
			l.id = "_im_password";
			l.size = "12";
			k.appendChild(l);
			d = document.createElement("BUTTON");
			d.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2004)));
			o.appendChild(d);
			a = document.createElement("BR");
			a.clear = "all";
			o.appendChild(a);
			if (this.isShowChangePassword && !INTERMediatorOnPage.isNativeAuth) {
				a = document.createElement("HR");
				o.appendChild(a);
				n = document.createElement("LABEL");
				o.appendChild(n);
				c = document.createElement("SPAN");
				c.style.minWidth = v;
				c.style.textAlign = "right";
				c.style.cssFloat = "left";
				c.style.fontSize = "0.7em";
				c.style.paddingTop = "4px";
				n.appendChild(c);
				c.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2006)));
				r = document.createElement("INPUT");
				r.type = "password";
				r.id = "_im_newpassword";
				r.size = "12";
				n.appendChild(r);
				p = document.createElement("BUTTON");
				p.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2005)));
				o.appendChild(p);
				q = document.createElement("DIV");
				q.style.textAlign = "center";
				q.style.textSize = "10pt";
				q.style.color = "#994433";
				o.appendChild(q)
			}
		}
		l.onkeydown = function (w) {
			m = (window.event) ? window.event.which : w.keyCode;
			if (m == 13) {
				d.onclick()
			}
		};
		s.value = INTERMediatorOnPage.authUser;
		s.onkeydown = function (w) {
			m = (window.event) ? window.event.which : w.keyCode;
			if (m == 13) {
				l.focus()
			}
		};
		d.onclick = function () {
			var y, w, x;
			y = document.getElementById("_im_username").value;
			w = document.getElementById("_im_password").value;
			INTERMediatorOnPage.authUser = y;
			b.removeChild(g);
			if (y != "" && (INTERMediatorOnPage.authChallenge == null || INTERMediatorOnPage.authChallenge.length < 24)) {
				INTERMediatorOnPage.authHashedPassword = "need-hash-pls";
				x = INTERMediator_DBAdapter.getChallenge();
				if (!x) {
					INTERMediator.flushMessage();
					return
				}
			}
			if (INTERMediatorOnPage.isNativeAuth) {
				INTERMediatorOnPage.authHashedPassword = w
			} else {
				INTERMediatorOnPage.authHashedPassword = SHA1(w + INTERMediatorOnPage.authUserSalt) + INTERMediatorOnPage.authUserHexSalt
			} if (INTERMediatorOnPage.authUser.length > 0) {
				INTERMediatorOnPage.storeCredencialsToCookie()
			}
			f();
			INTERMediator.flushMessage()
		};
		if (p) {
			p.onclick = function () {
				var C, x, y, z, B, w;
				C = document.getElementById("_im_username").value;
				x = document.getElementById("_im_password").value;
				y = document.getElementById("_im_newpassword").value;
				if (C === "" || x === "" || y === "") {
					q.innerHTML = INTERMediatorLib.getInsertedStringFromErrorNumber(2007);
					return
				}
				INTERMediatorOnPage.authUser = C;
				if (C != "" && (INTERMediatorOnPage.authChallenge == null || INTERMediatorOnPage.authChallenge.length < 24)) {
					INTERMediatorOnPage.authHashedPassword = "need-hash-pls";
					z = INTERMediator_DBAdapter.getChallenge();
					if (!z) {
						q.innerHTML = INTERMediatorLib.getInsertedStringFromErrorNumber(2008);
						INTERMediator.flushMessage();
						return
					}
				}
				INTERMediatorOnPage.authHashedPassword = SHA1(x + INTERMediatorOnPage.authUserSalt) + INTERMediatorOnPage.authUserHexSalt;
				B = "access=changepassword&newpass=" + INTERMediatorLib.generatePasswordHash(y);
				try {
					w = INTERMediator_DBAdapter.server_access(B, 1029, 1030)
				} catch (A) {
					w = {
						newPasswordResult: false
					}
				}
				q.innerHTML = INTERMediatorLib.getInsertedStringFromErrorNumber(w.newPasswordResult === true ? 2009 : 2010);
				INTERMediator.flushMessage()
			}
		}
		window.scroll(0, 0);
		s.focus();
		INTERMediatorOnPage.authCount++
	},
	authenticationError: function () {
		var c, b, a;
		INTERMediatorOnPage.hideProgress();
		c = document.getElementsByTagName("BODY")[0];
		b = document.createElement("div");
		c.insertBefore(b, c.childNodes[0]);
		b.style.height = "100%";
		b.style.width = "100%";
		b.style.backgroundImage = "url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAAACF0RVh0U29mdHdhcmUAR3JhcGhpY0NvbnZlcnRlciAoSW50ZWwpd4f6GQAAAHlJREFUeJzs0UENACAQA8EzdAl2EIEg3CKjyTGP/TfTur1OuJ2sAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAJwDRAekDAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADzAR4AAAD//wMAkUKRPI/rh/AAAAAASUVORK5CYII=)";
		b.style.position = "absolute";
		b.style.padding = " 50px 0 0 0";
		b.style.top = "0";
		b.style.left = "0";
		b.style.zIndex = "999999";
		a = document.createElement("div");
		a.style.width = "240px";
		a.style.backgroundColor = "#333333";
		a.style.color = "#DD6666";
		a.style.fontSize = "16pt";
		a.style.margin = "50px auto 0 auto";
		a.style.padding = "20px 4px 20px 4px";
		a.style.borderRadius = "10px";
		a.style.position = "relatvie";
		a.style.textAlign = "Center";
		a.onclick = function () {
			c.removeChild(b)
		};
		b.appendChild(a);
		a.appendChild(document.createTextNode(INTERMediatorLib.getInsertedStringFromErrorNumber(2001)))
	},
	INTERMediatorCheckBrowser: function (g) {
		var d, k, f, l, b, c, m, n, e, o, h, a;
		d = INTERMediatorOnPage.browserCompatibility();
		k = false;
		f = false;
		l;
		for (b in d) {
			if (navigator.userAgent.toUpperCase().indexOf(b.toUpperCase()) > -1) {
				k = true;
				if (d[b] instanceof Object) {
					for (c in d[b]) {
						if (navigator.platform.toUpperCase().indexOf(c.toUpperCase()) > -1) {
							f = true;
							l = d[b][c];
							break
						}
					}
				} else {
					f = true;
					l = d[b];
					break
				}
			}
		}
		m = false;
		if (k && f) {
			n = parseInt(l);
			if (navigator.appVersion.indexOf("MSIE") > -1) {
				o = navigator.appVersion.indexOf("MSIE");
				h = navigator.appVersion.indexOf(".", o);
				e = parseInt(navigator.appVersion.substring(o + 4, h))
			} else {
				h = navigator.appVersion.indexOf(".");
				e = parseInt(navigator.appVersion.substring(0, h))
			} if (l.indexOf("-") > -1) {
				m = (n >= e)
			} else {
				if (l.indexOf("+") > -1) {
					m = (n <= e)
				} else {
					m = (n == e)
				}
			}
		}
		if (m) {
			if (g != null) {
				g.parentNode.removeChild(g)
			}
		} else {
			a = document.getElementsByTagName("BODY")[0];
			a.innerHTML = '<div align="center"><font color="gray"><font size="+2">' + INTERMediatorOnPage.getMessages()[1022] + "</font><br>" + INTERMediatorOnPage.getMessages()[1023] + "<br>" + navigator.userAgent + "</font></div>"
		}
		return m
	},
	getNodeIdFromIMDefinition: function (c, e, a) {
		var b;
		if (a) {
			b = e
		} else {
			b = INTERMediatorLib.getParentRepeater(e)
		}
		return d(b, c);

		function d(l, f) {
			var h, g, m, k;
			if (l.nodeType != 1) {
				return null
			}
			h = l.childNodes;
			if (h) {
				for (g = 0; g < h.length; g++) {
					if (h[g].nodeType == 1) {
						if (INTERMediatorLib.isLinkedElement(h[g])) {
							m = INTERMediatorLib.getLinkedElementInfo(h[g]);
							if (m.indexOf(f) > -1) {
								k = h[g].getAttribute("id");
								return k
							}
						}
						k = d(h[g], f);
						if (k !== null) {
							return k
						}
					}
				}
			}
			return null
		}
	},
	getNodeIdFromIMDefinitionOnEnclosure: function (b, d) {
		var a;
		a = INTERMediatorLib.getParentEnclosure(d);
		return c(a, b);

		function c(k, e) {
			var g, f, l, h;
			if (k.nodeType != 1) {
				return null
			}
			g = k.childNodes;
			if (g) {
				for (f = 0; f < g.length; f++) {
					if (g[f].nodeType == 1) {
						if (INTERMediatorLib.isLinkedElement(g[f])) {
							l = INTERMediatorLib.getLinkedElementInfo(g[f]);
							if (l.indexOf(e) > -1) {
								h = g[f].getAttribute("id");
								return h
							}
						}
						h = c(g[f], e);
						if (h !== null) {
							return h
						}
					}
				}
			}
			return null
		}
	},
	getNodeIdsFromIMDefinition: function (c, f, a) {
		var b, d;
		if (a) {
			b = f
		} else {
			b = INTERMediatorLib.getParentEnclosure(f)
		} if (b != null) {
			d = [];
			e(b, c)
		}
		return d;

		function e(n, k) {
			var g, h, m, l;
			if (n.nodeType != 1) {
				return
			}
			m = n.childNodes;
			if (m) {
				for (l = 0; l < m.length; l++) {
					if (m[l].getAttribute != null) {
						g = m[l].getAttribute("class");
						h = m[l].getAttribute("title");
						if ((g != null && g.indexOf(k) > -1) || (h != null && h.indexOf(k) > -1)) {
							d.push(m[l].getAttribute("id"))
						}
					}
					e(m[l], k)
				}
			}
		}
	},
	getKeyWithRealm: function (a) {
		if (INTERMediatorOnPage.realm.length > 0) {
			return a + "_" + INTERMediatorOnPage.realm
		}
		return a
	},
	getCookie: function (c) {
		var d, b, a;
		d = document.cookie.split("; ");
		a = this.getKeyWithRealm(c);
		for (b = 0; b < d.length; b++) {
			if (d[b].indexOf(a + "=") == 0) {
				return decodeURIComponent(d[b].substring(d[b].indexOf("=") + 1))
			}
		}
		return ""
	},
	removeCookie: function (a) {
		document.cookie = this.getKeyWithRealm(a) + "=; path=/; max-age=0; expires=Thu, 1-Jan-1900 00:00:00 GMT;";
		document.cookie = this.getKeyWithRealm(a) + "=; max-age=0;  expires=Thu, 1-Jan-1900 00:00:00 GMT;"
	},
	setCookie: function (a, b) {
		this.setCookieWorker(this.getKeyWithRealm(a), b, false)
	},
	setCookieDomainWide: function (a, b) {
		this.setCookieWorker(this.getKeyWithRealm(a), b, true)
	},
	setCookieWorker: function (b, f, a) {
		var c;
		var e = new Date();
		e.setTime(e.getTime() + INTERMediatorOnPage.authExpired * 1000);
		document.cookie = b + "=" + encodeURIComponent(f) + (a ? ";path=/" : "") + ";max-age=" + INTERMediatorOnPage.authExpired + ";expires=" + e.toGMTString() + ";"
	},
	hideProgress: function () {
		var a;
		a = document.getElementById("_im_progress");
		if (a) {
			a.parentNode.removeChild(a)
		}
	},
	showProgress: function () {
		var d, b, a, c;
		b = document.getElementById("_im_progress");
		if (!b) {
			d = document.getElementsByTagName("BODY")[0];
			b = document.createElement("div");
			b.setAttribute("id", "_im_progress");
			b.style.backgroundColor = "#FFF";
			b.style.textAlign = "center";
			b.style.width = "130px";
			b.style.height = "75px";
			b.style.left = "0";
			b.style.top = "0";
			b.style.color = "#000";
			b.style.fontSize = "10px";
			b.style.position = "absolute";
			b.style.padding = "6px";
			b.style.borderRadius = "0 0 5px 0";
			b.style.borderRight = b.style.borderBottom = "solid 3px #000";
			b.style.zIndex = "999999";
			if (d.firstChild) {
				d.insertBefore(b, d.firstChild)
			} else {
				d.appendChild(b)
			}
			c = document.createElement("img");
			c.setAttribute("src", "data:image/gif;base64,R0lGODlhZABPANUAACIYFff396yoplZPTODf3y8mI4J8e8fFxEtDQI+LiWZmZu/v7rOvr9PR0D00MZmZmWJbWf///4yHhuTj4rq2tszMzJWRj3hzccG+vWZgXebe3kIxMVlSUNjW1XJraa2tpYaAfqKenGtjWrW1tVhQTd7W1nZwbpKNjOfm5ZmZmY6Jh8O/vgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAHAP8ALAAAAABkAE8AAAb/wIhwSCwaj8ikcslsOp/QqHRKrVqv2Kx2y+16v+CweBxdmACAQoLMpnYQ6Lhp0a43O4W4HkG3+414chNwaHx/hxECeWgPQgsZcQ4diHYCegJFZ2gFk5RkmmiYRqAFFJ5hZnGcQwIcE0MPl6deC4QAko6apax6BrNbgYV9bwAGB3C+QgeLACa/WIpyfYq7EakIrxHBAIbPUwl6a9Zn2EXUpta2uN5Pj6qixMmAyI4cqgfsd7ariQXVSNeygQLQKF8SCswMBWxyToglaQaNgJMjJB4UixGWxSkX0Zq9OKIaliH3itgmfAY7OFA1aSGVhu7iiPMWayMdjEhQ6CsmZCIa/w59TqWKk0xkkgIqAgxQoWShxk2dKKlUZcplEgt6AOhU0nBQOEo1C03CqSQrAKZLMBrQ48pPTDRz+v1TgsKsgyYLEVKtc2DlpqoknRwwC+BJ149oDAQN45NbSXpPKhC+CLmxA5RfTCZ26A/dk7pZEYx0rM3vZi8PmBXAZ/VIgBUhDAggMIRZKCEBGBgwIGFFgCKY5VZduzEqlgmIAWS4CVkJhKyrKOgp8Ju4ngpD3HHUBlmvzCypVRUUKcCzkQmEQ0QgEYdBhAFmC0QoIcAAMwdBF77lZjwK8j2vWAVOW0UEoAJhAEywQB4QROABYWos8FQcHJjTmRDeoTETFNF8x/8dT0T4ZAIFQT2HIAABNGBBAIOdKECLWYkyBEb7IdDfEg+JVU8a/cGYVQjb6GFCBh0QEEIABphgi5C1ECZaJmkUFEGGBWTThGkbKlNABkUs4OJUZoEQQR4geGWWLwNlBVwzi1kDCVxPgITEGUYs+eMCCZhWgAWvrGQAixcIecACyZlVBDhJvFmhE8xkmdGWRuRIGAemTFDBVhg0EMAkAjTgyCsTJGBbfGvGRcR/cDohqY2OwMFPEaadeAEBBGhiwgkRQODqikdIeEAIJvglzxBnqAGLalY2kSEA4pDFy4kAQBCAdGg4UIAvaaiyVVPBVQSZmT/dyMR+bbUmxJuRmBD/AkqSZVVhfG0ydGE/4llBJTpGObIWAiZ0G8CoPDkJhX7oAkCgFYQy+eGwS2BllnvwmSXuERhNWICU4OmBi7lIAKzVlIQxjIRIjbHahWYEcTaXa4Q1KATAdzUVWMJEoTLQcgs3Be+MkyVRsWrdfpHhxoEhQcACfhXQZrsAWLBAshZW0xhQbYCbsnBKrJXAb0Ys8ODE45BGs4Z/WKcccyD2ynUSa88D4jarITJ0S0VvIVJYBsdrh9UhzYsFwV/9YnZczk6BkWYrn9Kh2HVLIVKG23mDOEr5OuFSY6YatF+zcGTOBI0Fy9hRBHjjfA3YRIiEOOrsTMgRNaJ3XbTreo/ef+RJ3rJJcXN4OzN6EkMxkgt/UXs2UOy/H9E7K/6I4tLtaQSd/BG0595vc2CSNn0T4Jo81D8T4ry95YjF7VAGVkrq+/hQHH+E2cizz5AsRJAivfzKMpPM2OvgbzgzJtBMN/znBo9RjYAIs9P6EIgwXWCMgRCMoAQnSMEKWpB9QQAAOw==");
			c.setAttribute("width", 70 );
			c.style.marginRight = "12px";
			b.appendChild(c);
			a = document.createElement("img");
			a.setAttribute("src", "data:image/gif;base64,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");
			b.appendChild(a);
			b.appendChild(document.createElement("BR"));
			b.appendChild(document.createTextNode("COZMOZ working"))
		}
	}
};
var IMParts_tinymce = {
	instanciate: function (a) {
		var b = a.getAttribute("id") + "-e";
		this.ids.push(b);
		var c = document.createElement("TEXTAREA");
		c.setAttribute("id", b);
		INTERMediatorLib.setClassAttributeToNode(c, "_im_tinymce");
		a.appendChild(c);
		this.ids.push(b);
		c._im_getValue = function () {
			var d = c;
			return d.value
		};
		a._im_getValue = function () {
			var d = c;
			return d.value
		};
		a._im_getComponentId = function () {
			var d = b;
			return d
		};
		a._im_setValue = function (e) {
			var d = c;
			d.innerHTML = e
		}
	},
	ids: [],
	finish: function () {
		if (!tinymceOption) {
			tinymceOption = {}
		}
		tinymceOption.mode = "specific_textareas";
		tinymceOption.editor_selector = "_im_tinymce";
		tinymceOption.elements = this.ids.join(",");
		tinymceOption.setup = function (c) {
			c.onChange.add(function (d, e) {
				INTERMediator.valueChange(d.id)
			});
			c.onKeyDown.add(function (d, e) {
				INTERMediator.keyDown(e)
			});
			c.onKeyUp.add(function (d, e) {
				INTERMediator.keyUp(e)
			})
		};
		tinyMCE.init(tinymceOption);
		for (var a = 0; a < this.ids.length; a++) {
			var b = document.getElementById(this.ids[a]);
			if (b) {
				b._im_getValue = function () {
					return tinymce.EditorManager.get(this.id).getContent()
				}
			}
		}
	}
};
var IMParts_im_fileupload = {
	html5DDSuported: false,
	progressSupported: false,
	forceOldStyleForm: false,
	uploadId: "sign" + Math.random(),
	instanciate: function (d) {
		var g, a, b;
		var c = d.getAttribute("id") + "-e";
		var k = document.createElement("DIV");
		INTERMediatorLib.setClassAttributeToNode(k, "_im_fileupload");
		k.setAttribute("id", c);
		IMParts_im_fileupload.ids.push(c);
		if (IMParts_im_fileupload.forceOldStyleForm) {
			IMParts_im_fileupload.html5DDSuported = false
		} else {
			IMParts_im_fileupload.html5DDSuported = true;
			try {
				var h = new FileReader();
				var f = new FormData()
			} catch (e) {
				IMParts_im_fileupload.html5DDSuported = false
			}
		} if (IMParts_im_fileupload.html5DDSuported) {
			k.dropzone = "copy";
			k.style.width = "200px";
			k.style.height = "100px";
			k.style.paddingTop = "20px";
			k.style.backgroundColor = "#AAAAAA";
			k.style.border = "3px dotted #808080";
			k.style.textAlign = "center";
			k.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[3101]));
			if (IMParts_im_fileupload.progressSupported) {
				g = document.createElement("iframe");
				g.setAttribute("id", "upload_frame" + (IMParts_im_fileupload.ids.length - 1));
				g.setAttribute("name", "upload_frame");
				g.setAttribute("frameborder", "0");
				g.setAttribute("border", "0");
				g.setAttribute("scrolling", "no");
				g.setAttribute("scrollbar", "no");
				g.style.width = "100%";
				g.style.height = "24px";
				k.appendChild(g)
			}
		} else {
			a = document.createElement("FORM");
			a.setAttribute("method", "post");
			a.setAttribute("action", INTERMediatorOnPage.getEntryPath() + "?access=uploadfile");
			a.setAttribute("enctype", "multipart/form-data");
			k.appendChild(a);
			if (IMParts_im_fileupload.progressSupported) {
				g = document.createElement("INPUT");
				g.setAttribute("type", "hidden");
				g.setAttribute("name", "APC_UPLOAD_PROGRESS");
				g.setAttribute("id", "progress_key");
				g.setAttribute("value", IMParts_im_fileupload.uploadId + (IMParts_im_fileupload.ids.length - 1));
				a.appendChild(g)
			}
			g = document.createElement("INPUT");
			g.setAttribute("type", "hidden");
			g.setAttribute("name", "_im_redirect");
			g.setAttribute("value", location.href);
			a.appendChild(g);
			g = document.createElement("INPUT");
			g.setAttribute("type", "hidden");
			g.setAttribute("name", "_im_contextnewrecord");
			g.setAttribute("value", "uploadfile");
			a.appendChild(g);
			g = document.createElement("INPUT");
			g.setAttribute("type", "hidden");
			g.setAttribute("name", "access");
			g.setAttribute("value", "uploadfile");
			a.appendChild(g);
			g = document.createElement("INPUT");
			g.setAttribute("type", "file");
			g.setAttribute("accept", "*/*");
			g.setAttribute("name", "_im_uploadfile");
			a.appendChild(g);
			b = document.createElement("BUTTON");
			b.setAttribute("type", "submit");
			b.appendChild(document.createTextNode("送信"));
			a.appendChild(b);
			IMParts_im_fileupload.formFromId[c] = a
		}
		d.appendChild(k);
		k._im_getValue = function () {
			var l = k;
			return l.value
		};
		d._im_getValue = function () {
			var l = k;
			return l.value
		};
		d._im_getComponentId = function () {
			var l = c;
			return l
		};
		d._im_setValue = function (m) {
			var l = k;
			if (IMParts_im_fileupload.html5DDSuported) {} else {}
		}
	},
	ids: [],
	formFromId: {},
	finish: function () {
		if (IMParts_im_fileupload.html5DDSuported) {
			for (var c = 0; c < IMParts_im_fileupload.ids.length; c++) {
				var g = document.getElementById(IMParts_im_fileupload.ids[c]);
				if (g) {
					INTERMediatorLib.addEvent(g, "dragleave", function (h) {
						h.preventDefault();
						h.target.style.backgroundColor = "#AAAAAA"
					});
					INTERMediatorLib.addEvent(g, "dragover", function (h) {
						h.preventDefault();
						h.target.style.backgroundColor = "#AADDFF"
					});
					INTERMediatorLib.addEvent(g, "drop", (function () {
						var h = c;
						return function (o) {
							var n, p;
							o.preventDefault();
							var q = o.currentTarget;
							for (var m = 0; m < o.dataTransfer.files.length; m++) {
								n = o.dataTransfer.files[m];
								p = document.createElement("DIV");
								p.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[3102] + n.name));
								p.style.marginTop = "20px";
								p.style.backgroundColor = "#FFFFFF";
								p.style.textAlign = "center";
								o.target.appendChild(p)
							}
							var l = INTERMediator.updateRequiredObject[q.getAttribute("id")];
							if (IMParts_im_fileupload.progressSupported) {
								var k = document.getElementById("upload_frame" + h);
								k.style.display = "block";
								setTimeout(function () {
									var r = d() + "?uploadprocess=" + IMParts_im_fileupload.uploadId + h;
									k.setAttribute("src", r)
								})
							}
							INTERMediator_DBAdapter.uploadFile("&_im_contextname=" + encodeURIComponent(l.name) + "&_im_field=" + encodeURIComponent(l.field) + "&_im_keyfield=" + encodeURIComponent(l.keying.split("=")[0]) + "&_im_keyvalue=" + encodeURIComponent(l.keying.split("=")[1]) + "&_im_contextnewrecord=" + encodeURIComponent("uploadfile") + (IMParts_im_fileupload.progressSupported ? ("&APC_UPLOAD_PROGRESS=" + encodeURIComponent(IMParts_im_fileupload.uploadId + h)) : ""), {
								fileName: n.name,
								content: n
							}, function () {
								var u = true;
								var t = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", l.name);
								if (t["file-upload"]) {
									var s = "";
									for (var r = 0; r < t["file-upload"].length; r++) {
										if (t["file-upload"][r]["field"] == l.field) {
											s = t["file-upload"][r]["context"];
											break
										}
									}
									for (var r = 0; r < INTERMediator.keyFieldObject.length; r++) {
										if (INTERMediator.keyFieldObject[r]["name"] == s) {
											u = r;
											break
										}
									}
								}
								INTERMediator.construct(u)
							})
						}
					})())
				}
			}
		} else {
			for (var c = 0; c < IMParts_im_fileupload.ids.length; c++) {
				var g = document.getElementById(IMParts_im_fileupload.ids[c]);
				if (g) {
					var b = INTERMediator.updateRequiredObject[IMParts_im_fileupload.ids[c]];
					var f = g.getElementsByTagName("FORM")[0];
					var e = document.createElement("INPUT");
					e.setAttribute("type", "hidden");
					e.setAttribute("name", "_im_contextname");
					e.setAttribute("value", b.name);
					f.appendChild(e);
					e = document.createElement("INPUT");
					e.setAttribute("type", "hidden");
					e.setAttribute("name", "_im_field");
					e.setAttribute("value", b.field);
					f.appendChild(e);
					e = document.createElement("INPUT");
					e.setAttribute("type", "hidden");
					e.setAttribute("name", "_im_keyfield");
					e.setAttribute("value", b.keying.split("=")[0]);
					f.appendChild(e);
					e = document.createElement("INPUT");
					e.setAttribute("type", "hidden");
					e.setAttribute("name", "_im_keyvalue");
					e.setAttribute("value", b.keying.split("=")[1]);
					f.appendChild(e);
					e = document.createElement("INPUT");
					e.setAttribute("type", "hidden");
					e.setAttribute("name", "clientid");
					if (INTERMediatorOnPage.authUser.length > 0) {
						e.value = INTERMediatorOnPage.clientId
					}
					f.appendChild(e);
					e = document.createElement("INPUT");
					e.setAttribute("type", "hidden");
					e.setAttribute("name", "authuser");
					if (INTERMediatorOnPage.authUser.length > 0) {
						e.value = INTERMediatorOnPage.authUser
					}
					f.appendChild(e);
					e = document.createElement("INPUT");
					e.setAttribute("type", "hidden");
					e.setAttribute("name", "response");
					if (INTERMediatorOnPage.authUser.length > 0) {
						if (INTERMediatorOnPage.isNativeAuth) {
							thisForm.elements.response.value = INTERMediatorOnPage.publickey.biEncryptedString(INTERMediatorOnPage.authHashedPassword + "\n" + INTERMediatorOnPage.authChallenge)
						} else {
							if (INTERMediatorOnPage.authHashedPassword && INTERMediatorOnPage.authChallenge) {
								shaObj = new jsSHA(INTERMediatorOnPage.authHashedPassword, "ASCII");
								hmacValue = shaObj.getHMAC(INTERMediatorOnPage.authChallenge, "ASCII", "SHA-256", "HEX");
								e.value = hmacValue
							} else {
								e.value = "dummy"
							}
						}
					}
					f.appendChild(e);
					if (IMParts_im_fileupload.progressSupported) {
						e = document.createElement("iframe");
						e.setAttribute("id", "upload_frame" + c);
						e.setAttribute("name", "upload_frame");
						e.setAttribute("frameborder", "0");
						e.setAttribute("border", "0");
						e.setAttribute("scrolling", "no");
						e.setAttribute("scrollbar", "no");
						f.appendChild(e);
						INTERMediatorLib.addEvent(f, "submit", (function () {
							var h = c;
							return function (l) {
								var k = document.getElementById("upload_frame" + h);
								k.style.display = "block";
								setTimeout(function () {
									var m = d() + "?uploadprocess=" + IMParts_im_fileupload.uploadId + h;
									k.setAttribute("src", m)
								});
								return true
							}
						})())
					}
				}
			}
		}

		function d() {
			var k = document.getElementsByTagName("SCRIPT");
			for (var l = 0; l < k.length; l++) {
				var h = k[l].getAttribute("src");
				if (h.match(/\.php/)) {
					return h
				}
			}
			return null
		}

		function a(m, o) {
			var k, h, n, p;
			var l = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", m.name);
			if (l["file-upload"]) {
				for (k in l["file-upload"]) {
					if (l["file-upload"][k]["field"] == m.field) {
						p = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", l["file-upload"][k]["context"]);
						n = [{
							field: "path",
							value: o
						}];
						if (p.relation) {
							for (h in p.relation) {
								n.push({
									field: p.relation[h]["foreign-key"],
									value: m.keying.split("=")[1]
								})
							}
						}
						if (p.query) {
							for (h in p.query) {
								n.push({
									field: p.query[h]["field"],
									value: p.query[h]["value"]
								})
							}
						}
						INTERMediator_DBAdapter.db_createRecord({
							name: l["file-upload"][k]["context"],
							dataset: n
						})
					}
				}
			}
		}
	}
};
var INTERMediator = {
	debugMode: false,
	separator: "@",
	defDivider: "|",
	additionalCondition: [],
	additionalSortKey: [],
	defaultTargetInnerHTML: false,
	navigationLabel: null,
	startFrom: 0,
	widgetElementIds: [],
	radioNameMode: false,
	dontSelectRadioCheck: false,
	ignoreOptimisticLocking: false,
	supressDebugMessageOnPage: false,
	supressErrorMessageOnPage: false,
	additionalFieldValueOnNewRecord: [],
	waitSecondsAfterPostMessage: 4,
	pagedSize: 0,
	pagedAllCount: 0,
	currentEncNumber: 0,
	isIE: false,
	ieVersion: -1,
	titleAsLinkInfo: true,
	classAsLinkInfo: true,
	noRecordClassName: "_im_for_noresult_",
	updateRequiredObject: null,
	keyFieldObject: null,
	rootEnclosure: null,
	errorMessages: [],
	debugMessages: [],
	setDebugMessage: function (a, b) {
		if (b === undefined) {
			b = 1
		}
		if (INTERMediator.debugMode >= b) {
			INTERMediator.debugMessages.push(a);
			console.log("INTER-Mediator[DEBUG:%s]: %s", new Date(), a)
		}
	},
	setErrorMessage: function (a) {
		INTERMediator.errorMessages.push(a);
		console.error("INTER-Mediator[ERROR]: %s", a)
	},
	flushMessage: function () {
		var g, h, e, c, b, k, a, d, f;
		if (!INTERMediator.supressErrorMessageOnPage && INTERMediator.errorMessages.length > 0) {
			g = document.getElementById("_im_error_panel_4873643897897");
			if (g == null) {
				g = document.createElement("div");
				g.setAttribute("id", "_im_error_panel_4873643897897");
				g.style.backgroundColor = "#FFDDDD";
				h = document.createElement("h3");
				h.appendChild(document.createTextNode("Error Info from INTER-Mediator"));
				h.appendChild(document.createElement("hr"));
				g.appendChild(h);
				e = document.getElementsByTagName("body")[0];
				e.insertBefore(g, e.firstChild)
			}
			g.appendChild(document.createTextNode("============ERROR MESSAGE on " + new Date() + "============"));
			g.appendChild(document.createElement("hr"));
			for (c = 0; c < INTERMediator.errorMessages.length; c++) {
				k = INTERMediator.errorMessages[c].split("\n");
				for (b = 0; b < k.length; b++) {
					if (b > 0) {
						g.appendChild(document.createElement("br"))
					}
					g.appendChild(document.createTextNode(k[b]))
				}
				g.appendChild(document.createElement("hr"))
			}
		}
		if (!INTERMediator.supressDebugMessageOnPage && INTERMediator.debugMode && INTERMediator.debugMessages.length > 0) {
			g = document.getElementById("_im_debug_panel_4873643897897");
			if (g == null) {
				g = document.createElement("div");
				g.setAttribute("id", "_im_debug_panel_4873643897897");
				g.style.backgroundColor = "#DDDDDD";
				a = document.createElement("button");
				a.setAttribute("title", "clear");
				INTERMediatorLib.addEvent(a, "click", function () {
					f = document.getElementById("_im_debug_panel_4873643897897");
					f.parentNode.removeChild(f)
				});
				d = document.createTextNode("clear");
				a.appendChild(d);
				h = document.createElement("h3");
				h.appendChild(document.createTextNode("Debug Info from INTER-Mediator"));
				h.appendChild(a);
				h.appendChild(document.createElement("hr"));
				g.appendChild(h);
				e = document.getElementsByTagName("body")[0];
				if (e) {
					if (e.firstChild) {
						e.insertBefore(g, e.firstChild)
					} else {
						e.appendChild(g)
					}
				}
			}
			g.appendChild(document.createTextNode("============DEBUG INFO on " + new Date() + "============"));
			g.appendChild(document.createElement("hr"));
			for (c = 0; c < INTERMediator.debugMessages.length; c++) {
				k = INTERMediator.debugMessages[c].split("\n");
				for (b = 0; b < k.length; b++) {
					if (b > 0) {
						g.appendChild(document.createElement("br"))
					}
					g.appendChild(document.createTextNode(k[b]))
				}
				g.appendChild(document.createElement("hr"))
			}
		}
		INTERMediator.errorMessages = [];
		INTERMediator.debugMessages = []
	},
	isShiftKeyDown: false,
	isControlKeyDown: false,
	keyDown: function (a) {
		var b = (window.event) ? a.which : a.keyCode;
		if (b == 16) {
			INTERMediator.isShiftKeyDown = true
		}
		if (b == 17) {
			INTERMediator.isControlKeyDown = true
		}
	},
	keyUp: function (a) {
		var b = (window.event) ? a.which : a.keyCode;
		if (b == 16) {
			INTERMediator.isShiftKeyDown = false
		}
		if (b == 17) {
			INTERMediator.isControlKeyDown = false
		}
	},
	valueChange: function (idValue) {
		var changedObj, linkInfo, matched, context, i, index, checkFunction, target, value, result;
		if (INTERMediator.isShiftKeyDown && INTERMediator.isControlKeyDown) {
			INTERMediator.setDebugMessage("Canceled to update the value with shift+control keys.");
			INTERMediator.flushMessage();
			INTERMediator.isShiftKeyDown = false;
			INTERMediator.isControlKeyDown = false;
			return
		}
		INTERMediator.isShiftKeyDown = false;
		INTERMediator.isControlKeyDown = false;
		changedObj = document.getElementById(idValue);
		linkInfo = INTERMediatorLib.getLinkedElementInfo(changedObj);
		if (linkInfo.length > 0) {
			matched = linkInfo[0].match(/([^@]+)/);
			context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", matched[1]);
			if (context.validation != null) {
				for (i = 0; i < linkInfo.length; i++) {
					matched = linkInfo[i].match(/([^@]+)@([^@]+)/);
					for (index in context.validation) {
						if (context.validation[index]["field"] == matched[2]) {
							checkFunction = function () {
								target = changedObj;
								value = changedObj.value;
								result = false;
								eval("result = " + context.validation[index]["rule"]);
								if (!result) {
									alert(context.validation[index]["message"]);
									changedObj.value = INTERMediator.updateRequiredObject[idValue]["initialvalue"];
									changedObj.focus();
									if (INTERMediatorOnPage.doAfterValidationFailure != null) {
										INTERMediatorOnPage.doAfterValidationFailure(target, linkInfo[i])
									}
								} else {
									if (INTERMediatorOnPage.doAfterValidationSucceed != null) {
										INTERMediatorOnPage.doAfterValidationSucceed(target, linkInfo[i])
									}
								}
								return result
							};
							if (!checkFunction()) {
								return
							}
						}
					}
				}
			}
		}
		if (changedObj != null) {
			if (INTERMediatorOnPage.getOptionsTransaction() == "none") {
				INTERMediator.updateRequiredObject[idValue]["edit"] = true
			} else {
				INTERMediator.updateDB(idValue);
				INTERMediator.flushMessage()
			}
		}
	},
	updateDB: function (y) {
		var l = null,
			z, m, r, n, v, h, o, b, d, f, e, x, w, t, s, q, p, a, g, c;
		z = document.getElementById(y);
		if (z != null) {
			INTERMediatorOnPage.showProgress();
			INTERMediatorOnPage.retrieveAuthInfo();
			m = z.getAttribute("type");
			if (m == "radio" && !z.checked) {
				return
			}
			r = INTERMediator.updateRequiredObject[y];
			if (!INTERMediator.ignoreOptimisticLocking) {
				n = r.keying.split("=");
				v = n[0];
				n.shift();
				h = n.join("=");
				p = {
					name: r.name,
					records: 1,
					paging: r.paging,
					fields: [r.field],
					parentkeyvalue: null,
					conditions: [{
						field: v,
						operator: "=",
						value: h
					}],
					useoffset: false,
					primaryKeyOnly: true
				};
				try {
					o = INTERMediator_DBAdapter.db_query(p)
				} catch (u) {
					if (u == "_im_requath_request_") {
						if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
							INTERMediatorOnPage.authChallenge = null;
							INTERMediatorOnPage.authHashedPassword = null;
							INTERMediatorOnPage.authenticating(function () {
								INTERMediator.db_query(p)
							});
							return
						}
					} else {
						INTERMediator.setErrorMessage("EXCEPTION-1" + u.message)
					}
				}
				if (o.recordset == null || o.recordset[0] == null || o.recordset[0][r.field] == null) {
					alert(INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[1003], [r.field]));
					return
				}
				if (o.count > 1) {
					b = confirm(INTERMediatorOnPage.getMessages()[1024]);
					if (!b) {
						return
					}
				}
				o = o.recordset[0][r.field];
				d = (r.initialvalue != o)
			}
			if (INTERMediator.widgetElementIds.indexOf(z.getAttribute("id")) > -1) {
				l = z._im_getValue()
			} else {
				if (z.tagName == "TEXTAREA") {
					l = z.value
				} else {
					if (z.tagName == "SELECT") {
						l = z.value;
						if (z.firstChild.value == "") {
							z.removeChild(z.firstChild)
						}
					} else {
						if (z.tagName == "INPUT") {
							if (m != null) {
								if (m == "checkbox") {
									a = INTERMediatorOnPage.getDBSpecification();
									if (a["db-class"] != null && a["db-class"] == "FileMaker_FX") {
										g = [];
										c = z.parentNode.getElementsByTagName("INPUT");
										for (q = 0; q < c.length; q++) {
											if (c[q].checked) {
												g.push(c[q].getAttribute("value"))
											}
										}
										l = g.join("\n");
										d = (l == o)
									} else {
										f = z.getAttribute("value");
										if (z.checked) {
											l = f;
											d = (f == o)
										} else {
											l = "";
											d = (f != o)
										}
									}
								} else {
									if (m == "radio") {
										l = z.value
									} else {
										l = z.value
									}
								}
							}
						}
					}
				}
			}
		}
		if (d && !INTERMediator.ignoreOptimisticLocking) {
			if (!confirm(INTERMediatorLib.getInsertedString(INTERMediatorOnPage.getMessages()[1001], [r.initialvalue, l, o]))) {
				return
			}
			INTERMediatorOnPage.retrieveAuthInfo()
		}
		if (l != null) {
			e = r.keying.split("=");
			try {
				INTERMediator_DBAdapter.db_update({
					name: r.name,
					conditions: [{
						field: e[0],
						operator: "=",
						value: e[1]
					}],
					dataset: [{
						field: r.field,
						value: l
					}]
				})
			} catch (u) {
				if (u == "_im_requath_request_") {
					if (u == "_im_requath_request_") {
						if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
							INTERMediatorOnPage.authChallenge = null;
							INTERMediatorOnPage.authHashedPassword = null;
							INTERMediatorOnPage.authenticating(function () {
								INTERMediator.updateDB(y)
							});
							return
						}
					}
				} else {
					INTERMediator.setErrorMessage("EXCEPTION-2" + u.message)
				}
			}
			if (z.tagName == "INPUT" && m == "radio") {
				for (t in INTERMediator.updateRequiredObject) {
					if (INTERMediator.updateRequiredObject[t]["field"] == r.field) {
						INTERMediator.updateRequiredObject[t]["initialvalue"] = l
					}
				}
			} else {
				r.initialvalue = l
			}
			x = r.updatenodeid;
			w = false;
			for (t = 0; t < INTERMediator.keyFieldObject.length; t++) {
				for (s = 0; s < INTERMediator.keyFieldObject[t]["target"].length; s++) {
					if (INTERMediator.keyFieldObject[t]["target"][s] == y) {
						w = true
					}
				}
			}
			if (w) {
				for (t = 0; t < INTERMediator.keyFieldObject.length; t++) {
					if (INTERMediator.keyFieldObject[t]["node"].getAttribute("id") == x) {
						INTERMediator.constructMain(t);
						break
					}
				}
			}
		}
		INTERMediatorOnPage.hideProgress()
	},
	deleteButton: function (d, h, f, c, e) {
		var b, g;
		if (e) {
			if (!confirm(INTERMediatorOnPage.getMessages()[1025])) {
				return
			}
		}
		INTERMediatorOnPage.showProgress();
		try {
			INTERMediatorOnPage.retrieveAuthInfo();
			INTERMediator_DBAdapter.db_delete({
				name: d,
				conditions: [{
					field: h,
					operator: "=",
					value: f
				}]
			})
		} catch (a) {
			if (a == "_im_requath_request_") {
				if (INTERMediatorOnPage.requireAuthentication && !INTERMediatorOnPage.isComplementAuthData()) {
					INTERMediatorOnPage.authChallenge = null;
					INTERMediatorOnPage.authHashedPassword = null;
					INTERMediatorOnPage.authenticating(function () {
						INTERMediator.deleteButton(d, h, f, c, false)
					});
					return
				}
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-3" + a.message)
			}
		}
		for (b in c) {
			g = document.getElementById(c[b]);
			g.parentNode.removeChild(g)
		}
		INTERMediatorOnPage.hideProgress();
		
		
		//伊藤改造項目
		if( typeof( COZMOZ_DELETE_FUNTION ) == 'function' ){
			COZMOZ_DELETE_FUNTION();
		}
		
		
		INTERMediator.flushMessage()
	},
	insertButton: function (d, g, n, k, e) {
		var b, l, f, m, a, c;
		if (e) {
			if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
				return
			}
		}
		INTERMediatorOnPage.showProgress();
		b = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", d);
		l = [];
		if (g != null) {
			for (f in b.relation) {
				l.push({
					field: b.relation[f]["foreign-key"],
					value: g[b.relation[f]["join-field"]]
				})
			}
		}
		try {
			INTERMediatorOnPage.retrieveAuthInfo();
			INTERMediator_DBAdapter.db_createRecord({
				name: d,
				dataset: l
			})
		} catch (h) {
			if (h == "_im_requath_request_") {
				INTERMediatorOnPage.authChallenge = null;
				INTERMediatorOnPage.authHashedPassword = null;
				INTERMediatorOnPage.authenticating(function () {
					INTERMediator.insertButton(d, g, n, k, false)
				});
				INTERMediator.flushMessage();
				return
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-4" + h.message)
			}
		}
		for (m in k) {
			a = document.getElementById(k[m]);
			a.parentNode.removeChild(a)
		}
		for (c = 0; c < INTERMediator.keyFieldObject.length; c++) {
			if (INTERMediator.keyFieldObject[c]["node"].getAttribute("id") == n) {
				INTERMediator.keyFieldObject[c]["foreign-value"] = g;
				INTERMediator.constructMain(c);
				break
			}
		}
		INTERMediatorOnPage.hideProgress();
		INTERMediator.flushMessage()
	},
	insertRecordFromNavi: function (e, d, f) {
		var l, b, a, c, k, g;
		if (f) {
			if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
				return
			}
		}
		INTERMediatorOnPage.showProgress();
		b = INTERMediatorOnPage.getDataSources();
		a = null;
		for (l in b) {
			if (b[l]["name"] == e) {
				a = l;
				break
			}
		}
		if (a === null) {
			alert("no targetname :" + e);
			return
		}
		try {
			INTERMediatorOnPage.retrieveAuthInfo();
			c = INTERMediator_DBAdapter.db_createRecord({
				name: e,
				dataset: []
			})
		} catch (h) {
			if (h == "_im_requath_request_") {
				if (INTERMediatorOnPage.requireAuthentication) {
					if (!INTERMediatorOnPage.isComplementAuthData()) {
						INTERMediatorOnPage.authChallenge = null;
						INTERMediatorOnPage.authHashedPassword = null;
						INTERMediatorOnPage.authenticating(function () {
							INTERMediator.insertRecordFromNavi(e, d, f)
						});
						INTERMediator.flushMessage();
						return
					}
				}
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-5" + h.message)
			}
		}
		if (c > -1) {
			k = INTERMediator.additionalCondition;
			INTERMediator.startFrom = 0;
			g = {
				field: d,
				value: c
			};
			if (b[a]["records"] <= 1) {
				INTERMediator.additionalCondition = {};
				INTERMediator.additionalCondition[e] = g
			}
			INTERMediator.constructMain(true);
			INTERMediator.additionalCondition = k
		}
		INTERMediatorOnPage.hideProgress();
		INTERMediator.flushMessage()
	},
	deleteRecordFromNavi: function (b, e, d, c) {
		if (c) {
			if (!confirm(INTERMediatorOnPage.getMessages()[1026])) {
				return
			}
		}
		INTERMediatorOnPage.showProgress();
		try {
			INTERMediatorOnPage.retrieveAuthInfo();
			INTERMediator_DBAdapter.db_delete({
				name: b,
				conditions: [{
					field: e,
					operator: "=",
					value: d
				}]
			})
		} catch (a) {
			if (a == "_im_requath_request_") {
				INTERMediatorOnPage.authChallenge = null;
				INTERMediatorOnPage.authHashedPassword = null;
				INTERMediatorOnPage.authenticating(function () {
					INTERMediator.deleteRecordFromNavi(b, e, d, c)
				});
				INTERMediator.flushMessage();
				return
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-6" + a.message)
			}
		}
		if (INTERMediator.pagedAllCount - INTERMediator.startFrom < 2) {
			INTERMediator.startFrom--;
			if (INTERMediator.startFrom < 0) {
				INTERMediator.startFrom = 0
			}
		}
		INTERMediator.constructMain(true);
		INTERMediatorOnPage.hideProgress();
		INTERMediator.flushMessage()
	},
	saveRecordFromNavi: function () {
		var a;
		for (a in INTERMediator.updateRequiredObject) {
			if (INTERMediator.updateRequiredObject[a]["edit"]) {
				INTERMediator.updateDB(a)
			}
		}
		INTERMediator.flushMessage()
	},
	partialConstructing: false,
	objectReference: {},
	linkedElmCounter: 0,
	clickPostOnlyButton: function (m) {
		var q, p, c, f, s, l, e, r;
		var g, u, b, o;
		var d, n;
		var v = m.parentNode;
		while (!INTERMediatorLib.isEnclosure(v, true)) {
			v = v.parentNode;
			if (!v) {
				return
			}
		}
		d = [];
		n = [];
		for (q = 0; q < v.childNodes.length; q++) {
			a(v.childNodes[q])
		}
		l = {};
		for (q = 0; q < d.length; q++) {
			f = INTERMediatorLib.getLinkedElementInfo(d[q]);
			for (p = 0; p < f.length; p++) {
				s = f[p].split(INTERMediator.separator);
				if (!l[s[p]]) {
					l[s[p]] = 0
				}
				l[s[p]]++
			}
		}
		if (l.length < 1) {
			return
		}
		var h = -100;
		for (var t in l) {
			if (h < l[t]) {
				h = l[t];
				e = t
			}
		}
		c = [];
		for (q = 0; q < d.length; q++) {
			f = INTERMediatorLib.getLinkedElementInfo(d[q]);
			for (p = 0; p < f.length; p++) {
				s = f[p].split(INTERMediator.separator);
				if (s[0] == e) {
					if (INTERMediatorLib.isWidgetElement(d[q])) {
						c.push({
							field: s[1],
							value: d[q]._im_getValue()
						})
					} else {
						if (d[q].tagName == "SELECT") {
							c.push({
								field: s[1],
								value: d[q].value
							})
						} else {
							if (d[q].tagName == "TEXTAREA") {
								c.push({
									field: s[1],
									value: d[q].value
								})
							} else {
								if (d[q].tagName == "INPUT") {
									if ((d[q].getAttribute("type") == "radio") || (d[q].getAttribute("type") == "checkbox")) {
										if (d[q].checked) {
											c.push({
												field: s[1],
												value: d[q].value
											})
										}
									} else {
										c.push({
											field: s[1],
											value: d[q].value
										})
									}
								}
							}
						}
					}
				}
			}
		}
		for (q = 0; q < n.length; q++) {
			f = INTERMediatorLib.getNamedInfo(n[q]);
			for (p = 0; p < f.length; p++) {
				s = f[p].split(INTERMediator.separator);
				if (s[0] == e) {
					g = [];
					u = n[q].getElementsByTagName("INPUT");
					for (o = 0; o < u.length; o++) {
						b = u[o].getAttribute("type");
						if (b == "radio" || b == "checkbox") {
							if (u[o].checked) {
								g.push(u[o].value)
							}
						} else {
							g.push(u[o].value)
						}
					}
					c.push({
						field: s[1],
						value: g.join("\n") + "\n"
					})
				}
			}
		}
		r = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", e);
		INTERMediator_DBAdapter.db_createRecordWithAuth({
			name: e,
			dataset: c
		}, function (y) {
			var w, A, z = m,
				x = r,
				k = false;
			INTERMediator.flushMessage();
			if (INTERMediatorOnPage.processingAfterPostOnlyContext) {
				INTERMediatorOnPage.processingAfterPostOnlyContext(z)
			}
			if (x["post-dismiss-message"]) {
				A = z.parentNode;
				A.removeChild(z);
				w = document.createElement("SPAN");
				INTERMediatorLib.setClassAttributeToNode(w, "IM_POSTMESSAGE");
				w.appendChild(document.createTextNode(x["post-dismiss-message"]));
				A.appendChild(w);
				k = true
			}
			if (x["post-reconstruct"]) {
				setTimeout(function () {
					INTERMediator.construct(true)
				}, k ? INTERMediator.waitSecondsAfterPostMessage * 1000 : 0)
			}
			if (x["post-move-url"]) {
				setTimeout(function () {
					location.href = x["post-move-url"]
				}, k ? INTERMediator.waitSecondsAfterPostMessage * 1000 : 0)
			}
		});

		function a(x) {
			var w, k;
			if (x.nodeType === 1) {
				if (INTERMediatorLib.isLinkedElement(x)) {
					d.push(x)
				} else {
					if (INTERMediatorLib.isWidgetElement(x)) {
						d.push(x)
					} else {
						if (INTERMediatorLib.isNamedElement(x)) {
							n.push(x)
						} else {
							w = x.childNodes;
							for (k = 0; k < w.length; k++) {
								a(w[k])
							}
						}
					}
				}
			}
		}
	},
	construct: function (b) {
		var a;
		INTERMediatorOnPage.showProgress();
		if (b === true || b === undefined) {
			a = "INTERMediator.constructMain(true)"
		} else {
			a = "INTERMediator.constructMain(" + b + ")"
		}
		setTimeout(a, 0)
	},
	constructMain: function (indexOfKeyFieldObject) {
		var i, theNode, currentLevel = 0,
			postSetFields = [],
			buttonIdNum = 1,
			deleteInsertOnNavi = [],
			eventListenerPostAdding = [],
			isInsidePostOnly, nameAttrCounter = 1;
		INTERMediatorOnPage.retrieveAuthInfo();
		try {
			if (indexOfKeyFieldObject === true || indexOfKeyFieldObject === undefined) {
				this.partialConstructing = false;
				pageConstruct()
			} else {
				this.partialConstructing = true;
				partialConstruct(indexOfKeyFieldObject)
			}
		} catch (ex) {
			if (ex == "_im_requath_request_") {
				if (INTERMediatorOnPage.requireAuthentication) {
					if (!INTERMediatorOnPage.isComplementAuthData()) {
						INTERMediatorOnPage.authChallenge = null;
						INTERMediatorOnPage.authHashedPassword = null;
						INTERMediatorOnPage.authenticating(function () {
							INTERMediator.constructMain(indexOfKeyFieldObject)
						});
						return
					}
				}
			} else {
				INTERMediator.setErrorMessage("EXCEPTION-7" + ex.message)
			}
		}
		INTERMediatorOnPage.hideProgress();
		for (i = 0; i < eventListenerPostAdding.length; i++) {
			theNode = document.getElementById(eventListenerPostAdding[i].id);
			if (theNode) {
				INTERMediatorLib.addEvent(theNode, eventListenerPostAdding[i].event, eventListenerPostAdding[i].todo)
			}
		}
		if (INTERMediatorOnPage.doAfterConstruct) {
			INTERMediatorOnPage.doAfterConstruct()
		}
		INTERMediator.flushMessage();

		function partialConstruct(indexOfKeyFieldObject) {
			var updateNode, originalNodes, i, beforeKeyFieldObjectCount, currentNode, currentID, enclosure, field, targetNode;
			isInsidePostOnly = false;
			updateNode = INTERMediator.keyFieldObject[indexOfKeyFieldObject]["node"];
			while (updateNode.firstChild) {
				updateNode.removeChild(updateNode.firstChild)
			}
			originalNodes = INTERMediator.keyFieldObject[indexOfKeyFieldObject]["original"];
			for (i = 0; i < originalNodes.length; i++) {
				updateNode.appendChild(originalNodes[i])
			}
			beforeKeyFieldObjectCount = INTERMediator.keyFieldObject.length;
			postSetFields = [];
			try {
				seekEnclosureNode(updateNode, INTERMediator.keyFieldObject[indexOfKeyFieldObject]["foreign-value"], INTERMediator.keyFieldObject[indexOfKeyFieldObject]["name"], INTERMediatorLib.getEnclosureSimple(updateNode), null)
			} catch (ex) {
				if (ex == "_im_requath_request_") {
					throw ex
				} else {
					INTERMediator.setErrorMessage("EXCEPTION-8" + ex.message)
				}
			}
			for (i = 0; i < postSetFields.length; i++) {
				document.getElementById(postSetFields[i]["id"]).value = postSetFields[i]["value"]
			}
			for (i = beforeKeyFieldObjectCount + 1; i < INTERMediator.keyFieldObject.length; i++) {
				currentNode = INTERMediator.keyFieldObject[i];
				currentID = currentNode.node.getAttribute("id");
				if (currentNode.target == null) {
					if (currentID != null && currentID.match(/IM[0-9]+-[0-9]+/)) {
						enclosure = INTERMediatorLib.getParentRepeater(currentNode.node)
					} else {
						enclosure = INTERMediatorLib.getParentRepeater(INTERMediatorLib.getParentEnclosure(currentNode.node))
					} if (enclosure != null) {
						for (field in currentNode["foreign-value"]) {
							targetNode = getEnclosedNode(enclosure, currentNode.name, field);
							if (targetNode) {
								currentNode.target = targetNode.getAttribute("id")
							}
						}
					}
				}
			}
		}

		function pageConstruct() {
			var ua, msiePos, i, c, bodyNode, currentNode, currentID, enclosure, targetNode, emptyElement;
			INTERMediator.keyFieldObject = [];
			INTERMediator.updateRequiredObject = {};
			INTERMediator.currentEncNumber = 1;
			INTERMediator.widgetElementIds = [];
			isInsidePostOnly = false;
			ua = navigator.userAgent;
			msiePos = ua.toLocaleUpperCase().indexOf("MSIE");
			if (msiePos >= 0) {
				INTERMediator.isIE = true;
				for (i = msiePos + 4; i < ua.length; i++) {
					c = ua.charAt(i);
					if (c != " " && c != "." && (c < "0" || c > "9")) {
						INTERMediator.ieVersion = INTERMediatorLib.toNumber(ua.substring(msiePos + 4, i));
						break
					}
				}
			}
			bodyNode = document.getElementsByTagName("BODY")[0];
			if (INTERMediator.rootEnclosure == null) {
				INTERMediator.rootEnclosure = bodyNode.innerHTML
			} else {
				bodyNode.innerHTML = INTERMediator.rootEnclosure
			}
			postSetFields = [];
			try {
				seekEnclosureNode(bodyNode, null, null, null, null)
			} catch (ex) {
				if (ex == "_im_requath_request_") {
					throw ex
				} else {
					INTERMediator.setErrorMessage("EXCEPTION-9" + ex.message)
				}
			}
			for (i = 0; i < postSetFields.length; i++) {
				if (postSetFields[i]["value"] == "" && document.getElementById(postSetFields[i]["id"]).tagName == "SELECT") {
					emptyElement = document.createElement("option");
					emptyElement.setAttribute("value", "");
					document.getElementById(postSetFields[i]["id"]).insertBefore(emptyElement, document.getElementById(postSetFields[i]["id"]).firstChild)
				}
				document.getElementById(postSetFields[i]["id"]).value = postSetFields[i]["value"]
			}
			for (i = 0; i < INTERMediator.keyFieldObject.length; i++) {
				currentNode = INTERMediator.keyFieldObject[i];
				currentID = currentNode.node.getAttribute("id");
				if (currentNode.target == null) {
					if (currentID != null && currentID.match(/IM[0-9]+-[0-9]+/)) {
						enclosure = INTERMediatorLib.getParentRepeater(currentNode.node)
					} else {
						enclosure = INTERMediatorLib.getParentRepeater(INTERMediatorLib.getParentEnclosure(currentNode.node))
					} if (enclosure != null) {
						targetNode = getEnclosedNode(enclosure, currentNode.name, currentNode.field);
						if (targetNode) {
							currentNode.target = targetNode.getAttribute("id")
						}
					}
				}
			}
			navigationSetup();
			//appendCredit()
		}

		function seekEnclosureNode(node, currentRecord, currentTable, parentEnclosure, objectReference) {
			var children, className, i;
			if (node.nodeType === 1) {
				try {
					if (INTERMediatorLib.isEnclosure(node, false)) {
						className = INTERMediatorLib.getClassAttributeFromNode(node);
						if (className && className.match(/_im_post/)) {
							setupPostOnlyEnclosure(node)
						} else {
							if (INTERMediator.isIE) {
								try {
									expandEnclosure(node, currentRecord, currentTable, parentEnclosure, objectReference)
								} catch (ex) {
									if (ex == "_im_requath_request_") {
										throw ex
									}
								}
							} else {
								expandEnclosure(node, currentRecord, currentTable, parentEnclosure, objectReference)
							}
						}
					} else {
						children = node.childNodes;
						if (children) {
							for (i = 0; i < children.length; i++) {
								if (children[i].nodeType === 1) {
									seekEnclosureNode(children[i], currentRecord, currentTable, parentEnclosure, objectReference)
								}
							}
						}
					}
				} catch (ex) {
					if (ex == "_im_requath_request_") {
						throw ex
					} else {
						INTERMediator.setErrorMessage("EXCEPTION-10" + ex.message)
					}
				}
			}
		}

		function setupPostOnlyEnclosure(node) {
			var nodes;
			var postNodes = INTERMediatorLib.getElementsByClassName(node, "_im_post");
			for (var i = 1; i < postNodes.length; i++) {
				INTERMediatorLib.addEvent(postNodes[i], "click", (function () {
					var targetNode = postNodes[i];
					return function () {
						INTERMediator.clickPostOnlyButton(targetNode)
					}
				})())
			}
			nodes = node.childNodes;
			isInsidePostOnly = true;
			for (i = 0; i < nodes.length; i++) {
				seekEnclosureInPostOnly(nodes[i])
			}
			isInsidePostOnly = false;

			function seekEnclosureInPostOnly(node) {
				var children, i;
				if (node.nodeType === 1) {
					try {
						if (INTERMediatorLib.isEnclosure(node, false)) {
							expandEnclosure(node, null, null, null, null)
						} else {
							children = node.childNodes;
							for (i = 0; i < children.length; i++) {
								seekEnclosureInPostOnly(children[i])
							}
						}
					} catch (ex) {
						if (ex == "_im_requath_request_") {
							throw ex
						} else {
							INTERMediator.setErrorMessage("EXCEPTION-11" + ex.message)
						}
					}
				}
			}
		}

		function expandEnclosure(node, currentRecord, currentTable, parentEnclosure, parentObjectInfo) {
			var objectReference = {}, linkedNodes, encNodeTag, parentNodeId, repeatersOriginal, repeaters, linkDefs, voteResult, currentContext, fieldList, repNodeTag, relationValue, dependObject, relationDef, index, fieldName, thisKeyFieldObject, i, j, k, ix, targetRecords, newNode, nodeClass, repeatersOneRec, currentLinkedNodes, shouldDeleteNodes, keyField, keyValue, counter, nodeTag, typeAttr, linkInfoArray, RecordCounter, valueChangeFunction, nInfo, curVal, curTarget, postCallFunc, newlyAddedNodes, keyingValue, oneRecord, isMatch, pagingValue, recordsValue, currentWidgetNodes, widgetSupport, nodeId, nameAttr, nameNumber, nameTable;
			currentLevel++;
			INTERMediator.currentEncNumber++;
			widgetSupport = {};
			if (!node.getAttribute("id")) {
				node.setAttribute("id", nextIdValue())
			}
			encNodeTag = node.tagName;
			parentNodeId = (parentEnclosure == null ? null : parentEnclosure.getAttribute("id"));
			repNodeTag = INTERMediatorLib.repeaterTagFromEncTag(encNodeTag);
			repeatersOriginal = collectRepeatersOriginal(node, repNodeTag);
			repeaters = collectRepeaters(repeatersOriginal);
			linkedNodes = collectLinkedElement(repeaters).linkedNode;
			linkDefs = collectLinkDefinitions(linkedNodes);
			voteResult = tableVoting(linkDefs);
			currentContext = voteResult.targettable;
			fieldList = voteResult.fieldlist;
			if (currentContext) {
				relationValue = null;
				dependObject = [];
				relationDef = currentContext.relation;
				if (relationDef) {
					relationValue = {};
					for (index in relationDef) {
						relationValue[relationDef[index]["join-field"]] = currentRecord[relationDef[index]["join-field"]];
						for (fieldName in parentObjectInfo) {
							if (fieldName == relationDef[index]["join-field"]) {
								dependObject.push(parentObjectInfo[fieldName])
							}
						}
					}
				}
				thisKeyFieldObject = {
					node: node,
					name: currentContext.name,
					"foreign-value": relationValue,
					parent: node.parentNode,
					original: [],
					target: dependObject
				};
				for (i = 0; i < repeatersOriginal.length; i++) {
					thisKeyFieldObject.original.push(repeatersOriginal[i].cloneNode(true))
				}
				INTERMediator.keyFieldObject.push(thisKeyFieldObject);
				pagingValue = false;
				if (currentContext.paging) {
					pagingValue = currentContext.paging
				}
				recordsValue = 10000000000;
				if (currentContext.records) {
					recordsValue = currentContext.records
				}
				if (currentContext.cache == true) {
					if (!INTERMediatorOnPage.dbCache[currentContext.name]) {
						INTERMediatorOnPage.dbCache[currentContext.name] = INTERMediator_DBAdapter.db_query({
							name: currentContext.name,
							records: null,
							paging: null,
							fields: fieldList,
							parentkeyvalue: null,
							conditions: null,
							useoffset: false
						})
					}
					if (relationValue == null) {
						targetRecords = INTERMediatorOnPage.dbCache[currentContext.name]
					} else {
						targetRecords = {
							recordset: [],
							count: 0
						};
						counter = 0;
						for (ix in INTERMediatorOnPage.dbCache[currentContext.name].recordset) {
							oneRecord = INTERMediatorOnPage.dbCache[currentContext.name].recordset[ix];
							isMatch = true;
							index = 0;
							for (keyField in relationValue) {
								fieldName = currentContext.relation[index]["foreign-key"];
								if (oneRecord[fieldName] != relationValue[keyField]) {
									isMatch = false;
									break
								}
								index++
							}
							if (isMatch) {
								if (!pagingValue || (pagingValue && (counter >= INTERMediator.startFrom))) {
									targetRecords.recordset.push(oneRecord);
									targetRecords.count++;
									if (recordsValue <= targetRecords.count) {
										break
									}
								}
								counter++
							}
						}
					}
				} else {
					try {
						targetRecords = INTERMediator_DBAdapter.db_query({
							name: currentContext.name,
							records: currentContext.records,
							paging: currentContext.paging,
							fields: fieldList,
							parentkeyvalue: relationValue,
							conditions: null,
							useoffset: true
						})
					} catch (ex) {
						if (ex == "_im_requath_request_") {
							throw ex
						} else {
							INTERMediator.setErrorMessage("EXCEPTION-12" + ex.message)
						}
					}
				} if (targetRecords.count == 0) {
					for (i = 0; i < repeaters.length; i++) {
						newNode = repeaters[i].cloneNode(true);
						nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
						if (nodeClass == INTERMediator.noRecordClassName) {
							node.appendChild(newNode);
							if (newNode.getAttribute("id") == null) {
								newNode.setAttribute("id", nextIdValue())
							}
						}
					}
				}
				RecordCounter = 0;
				for (ix in targetRecords.recordset) {
					RecordCounter++;
					repeatersOneRec = cloneEveryNodes(repeatersOriginal);
					currentWidgetNodes = collectLinkedElement(repeatersOneRec).widgetNode;
					currentLinkedNodes = collectLinkedElement(repeatersOneRec).linkedNode;
					shouldDeleteNodes = shouldDeleteNodeIds(repeatersOneRec);
					keyField = currentContext.key ? currentContext.key : "id";
					keyValue = targetRecords.recordset[ix][keyField];
					keyingValue = keyField + "=" + keyValue;
					for (k = 0; k < currentLinkedNodes.length; k++) {
						if (currentLinkedNodes[k].getAttribute("id") == null) {
							currentLinkedNodes[k].setAttribute("id", nextIdValue())
						}
					}
					for (k = 0; k < currentWidgetNodes.length; k++) {
						var wInfo = INTERMediatorLib.getWidgetInfo(currentWidgetNodes[k]);
						if (wInfo[0]) {
							if (!widgetSupport[wInfo[0]]) {
								var targetName = "IMParts_" + wInfo[0];
								widgetSupport[wInfo[0]] = {
									plugin: eval(targetName),
									instanciate: eval(targetName + ".instanciate"),
									finish: eval(targetName + ".finish")
								}
							}(widgetSupport[wInfo[0]].instanciate).apply((widgetSupport[wInfo[0]].plugin), [currentWidgetNodes[k]])
						}
					}
					nameTable = {};
					for (k = 0; k < currentLinkedNodes.length; k++) {
						nodeTag = currentLinkedNodes[k].tagName;
						nodeId = currentLinkedNodes[k].getAttribute("id");
						if (INTERMediatorLib.isWidgetElement(currentLinkedNodes[k])) {
							nodeId = currentLinkedNodes[k]._im_getComponentId();
							INTERMediator.widgetElementIds.push(nodeId)
						}
						typeAttr = currentLinkedNodes[k].getAttribute("type");
						linkInfoArray = INTERMediatorLib.getLinkedElementInfo(currentLinkedNodes[k]);
						if (typeAttr == "radio") {
							nameTableKey = linkInfoArray.join("|");
							if (!nameTable[nameTableKey]) {
								nameTable[nameTableKey] = nameAttrCounter;
								nameAttrCounter++
							}
							nameNumber = nameTable[nameTableKey];
							nameAttr = currentLinkedNodes[k].getAttribute("name");
							if (nameAttr) {
								currentLinkedNodes[k].setAttribute("name", nameAttr + "-" + nameNumber)
							} else {
								currentLinkedNodes[k].setAttribute("name", "IM-R-" + nameNumber)
							}
						}
						if (!isInsidePostOnly && (nodeTag == "INPUT" || nodeTag == "SELECT" || nodeTag == "TEXTAREA")) {
							valueChangeFunction = function (targetId) {
								var theId = targetId;
								return function (evt) {
									INTERMediator.valueChange(theId)
								}
							};
							eventListenerPostAdding.push({
								id: nodeId,
								event: "change",
								todo: valueChangeFunction(nodeId)
							});
							if (nodeTag != "SELECT") {
								eventListenerPostAdding.push({
									id: nodeId,
									event: "keydown",
									todo: INTERMediator.keyDown
								});
								eventListenerPostAdding.push({
									id: nodeId,
									event: "keyup",
									todo: INTERMediator.keyUp
								})
							}
						}
						for (j = 0; j < linkInfoArray.length; j++) {
							nInfo = INTERMediatorLib.getNodeInfoArray(linkInfoArray[j]);
							curVal = targetRecords.recordset[ix][nInfo.field];
							if (curVal == null) {
								curVal = ""
							}
							curTarget = nInfo.target;
							if (nodeTag == "INPUT" || nodeTag == "SELECT" || nodeTag == "TEXTAREA" || INTERMediatorLib.isWidgetElement(currentLinkedNodes[k])) {
								INTERMediator.updateRequiredObject[nodeId] = {
									targetattribute: curTarget,
									initialvalue: curVal,
									name: currentContext.name,
									field: nInfo.field,
									"parent-enclosure": node.getAttribute("id"),
									keying: keyingValue,
									"foreign-value": relationValue,
									updatenodeid: parentNodeId
								}
							}
							objectReference[nInfo.field] = nodeId;
							if (setDataToElement(currentLinkedNodes[k], curTarget, curVal)) {
								postSetFields.push({
									id: nodeId,
									value: curVal
								})
							}
						}
					}
					setupDeleteButton(encNodeTag, repNodeTag, repeatersOneRec[repeatersOneRec.length - 1], currentContext, keyField, keyValue, shouldDeleteNodes);
					newlyAddedNodes = [];
					for (i = 0; i < repeatersOneRec.length; i++) {
						newNode = repeatersOneRec[i].cloneNode(true);
						nodeClass = INTERMediatorLib.getClassAttributeFromNode(newNode);
						if (nodeClass != INTERMediator.noRecordClassName) {
							node.appendChild(newNode);
							newlyAddedNodes.push(newNode);
							if (newNode.getAttribute("id") == null) {
								newNode.setAttribute("id", nextIdValue())
							}
							seekEnclosureNode(newNode, targetRecords.recordset[ix], currentContext.name, node, objectReference)
						}
					}
					try {
						if (INTERMediatorOnPage.expandingRecordFinish != null) {
							INTERMediatorOnPage.expandingRecordFinish(currentContext.name, newlyAddedNodes);
							INTERMediator.setDebugMessage("Call INTERMediatorOnPage.expandingRecordFinish with the context: " + currentContext.name, 2)
						}
						if (currentContext["post-repeater"]) {
							postCallFunc = new Function("arg", "INTERMediatorOnPage." + currentContext["post-repeater"] + "(arg)");
							postCallFunc(newlyAddedNodes);
							INTERMediator.setDebugMessage("Call the post repeater method 'INTERMediatorOnPage." + currentContext["post-repeater"] + "' with the context: " + currentContext.name, 2)
						}
					} catch (ex) {
						if (ex == "_im_requath_request_") {
							throw ex
						} else {
							INTERMediator.setErrorMessage("EXCEPTION-23" + ex.message)
						}
					}
				}
				setupInsertButton(currentContext, encNodeTag, repNodeTag, node, relationValue);
				for (var pName in widgetSupport) {
					widgetSupport[pName].plugin.finish()
				}
				try {
					if (INTERMediatorOnPage.expandingEnclosureFinish != null) {
						INTERMediatorOnPage.expandingEnclosureFinish(currentContext.name, node);
						INTERMediator.setDebugMessage("Call INTERMediatorOnPage.expandingEnclosureFinish with the context: " + currentContext.name, 2)
					}
				} catch (ex) {
					if (ex == "_im_requath_request_") {
						throw ex
					} else {
						INTERMediator.setErrorMessage("EXCEPTION-21" + ex.message)
					}
				}
				try {
					if (currentContext["post-enclosure"]) {
						postCallFunc = new Function("arg", "INTERMediatorOnPage." + currentContext["post-enclosure"] + "(arg)");
						postCallFunc(node);
						INTERMediator.setDebugMessage("Call the post enclosure method 'INTERMediatorOnPage." + currentContext["post-enclosure"] + "' with the context: " + currentContext.name, 2)
					}
				} catch (ex) {
					if (ex == "_im_requath_request_") {
						throw ex
					} else {
						INTERMediator.setErrorMessage("EXCEPTION-22:[" + ex.message + "] hint: post-enclosure of " + currentContext.name)
					}
				}
			} else {
				repeaters = [];
				for (i = 0; i < repeatersOriginal.length; i++) {
					newNode = node.appendChild(repeatersOriginal[i]);
					seekEnclosureNode(newNode, null, null, node, null)
				}
			}
			currentLevel--
		}

		function nextIdValue() {
			INTERMediator.linkedElmCounter++;
			return currentIdValue()
		}

		function currentIdValue() {
			return "IM" + INTERMediator.currentEncNumber + "-" + INTERMediator.linkedElmCounter
		}

		function collectRepeatersOriginal(node, repNodeTag) {
			var i, repeatersOriginal = [],
				children;
			children = node.childNodes;
			for (i = 0; i < children.length; i++) {
				if (children[i].nodeType === 1 && children[i].tagName == repNodeTag) {
					repeatersOriginal.push(children[i])
				}
			}
			return repeatersOriginal
		}

		function collectRepeaters(repeatersOriginal) {
			var i, repeaters = [],
				inDocNode, parentOfRep, cloneNode;
			for (i = 0; i < repeatersOriginal.length; i++) {
				inDocNode = repeatersOriginal[i];
				parentOfRep = repeatersOriginal[i].parentNode;
				cloneNode = repeatersOriginal[i].cloneNode(true);
				repeaters.push(cloneNode);
				cloneNode.setAttribute("id", nextIdValue());
				parentOfRep.removeChild(inDocNode)
			}
			return repeaters
		}
		var linkedNodesCollection;
		var widgetNodesCollection;

		function collectLinkedElement(repeaters) {
			var i;
			linkedNodesCollection = [];
			widgetNodesCollection = [];
			for (i = 0; i < repeaters.length; i++) {
				seekLinkedElement(repeaters[i])
			}
			return {
				linkedNode: linkedNodesCollection,
				widgetNode: widgetNodesCollection
			}
		}

		function seekLinkedElement(node) {
			var nType, currentEnclosure, children, detectedEnclosure, i;
			nType = node.nodeType;
			if (nType === 1) {
				if (INTERMediatorLib.isLinkedElement(node)) {
					currentEnclosure = INTERMediatorLib.getEnclosure(node);
					if (currentEnclosure === null) {
						linkedNodesCollection.push(node)
					} else {
						return currentEnclosure
					}
				}
				if (INTERMediatorLib.isWidgetElement(node)) {
					currentEnclosure = INTERMediatorLib.getEnclosure(node);
					if (currentEnclosure === null) {
						widgetNodesCollection.push(node)
					} else {
						return currentEnclosure
					}
				}
				children = node.childNodes;
				for (i = 0; i < children.length; i++) {
					detectedEnclosure = seekLinkedElement(children[i])
				}
			}
			return null
		}

		function collectLinkDefinitions(linkedNodes) {
			var linkDefs = [],
				nodeDefs, j, k;
			for (j = 0; j < linkedNodes.length; j++) {
				nodeDefs = INTERMediatorLib.getLinkedElementInfo(linkedNodes[j]);
				if (nodeDefs !== null) {
					for (k = 0; k < nodeDefs.length; k++) {
						linkDefs.push(nodeDefs[k])
					}
				}
			}
			return linkDefs
		}

		function tableVoting(linkDefs) {
			var j, nodeInfoArray, nodeInfoField, nodeInfoTable, maxVoted, maxTableName, tableName, context, tableVote = [],
				fieldList = [];
			for (j = 0; j < linkDefs.length; j++) {
				nodeInfoArray = INTERMediatorLib.getNodeInfoArray(linkDefs[j]);
				nodeInfoField = nodeInfoArray.field;
				nodeInfoTable = nodeInfoArray.table;
				if (nodeInfoField != null && nodeInfoTable != null && nodeInfoField.length != 0 && nodeInfoTable.length != 0) {
					if (fieldList[nodeInfoTable] == null) {
						fieldList[nodeInfoTable] = []
					}
					fieldList[nodeInfoTable].push(nodeInfoField);
					if (tableVote[nodeInfoTable] == null) {
						tableVote[nodeInfoTable] = 1
					} else {
						++tableVote[nodeInfoTable]
					}
				} else {
					INTERMediator.setErrorMessage(INTERMediatorLib.getInsertedStringFromErrorNumber(1006, [linkDefs[j]]))
				}
			}
			maxVoted = -1;
			maxTableName = "";
			for (tableName in tableVote) {
				if (maxVoted < tableVote[tableName]) {
					maxVoted = tableVote[tableName];
					maxTableName = tableName
				}
			}
			context = INTERMediatorLib.getNamedObject(INTERMediatorOnPage.getDataSources(), "name", maxTableName);
			return {
				targettable: context,
				fieldlist: fieldList[maxTableName]
			}
		}

		function cloneEveryNodes(originalNodes) {
			var i, clonedNodes = [];
			for (i = 0; i < originalNodes.length; i++) {
				clonedNodes.push(originalNodes[i].cloneNode(true))
			}
			return clonedNodes
		}

		function shouldDeleteNodeIds(repeatersOneRec) {
			var shouldDeleteNodes = [],
				i;
			for (i = 0; i < repeatersOneRec.length; i++) {
				if (repeatersOneRec[i].getAttribute("id") == null) {
					repeatersOneRec[i].setAttribute("id", nextIdValue())
				}
				shouldDeleteNodes.push(repeatersOneRec[i].getAttribute("id"))
			}
			return shouldDeleteNodes
		}

		function setDataToElement(element, curTarget, curVal) {
			var styleName, statement, currentValue, scriptNode, typeAttr, valueAttr, textNode, needPostValueSet = false,
				nodeTag, curValues, i;
			nodeTag = element.tagName;
			if (curTarget != null && curTarget.length > 0) {
				if (curTarget.charAt(0) == "#") {
					curTarget = curTarget.substring(1);
					if (curTarget == "innerHTML") {
						if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
							curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>")
						}
						element.innerHTML += curVal
					} else {
						if (curTarget == "textNode" || curTarget == "script") {
							textNode = document.createTextNode(curVal);
							if (nodeTag == "TEXTAREA") {
								curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r")
							}
							element.appendChild(textNode)
						} else {
							if (curTarget.indexOf("style.") == 0) {
								styleName = curTarget.substring(6, curTarget.length);
								statement = "element.style." + styleName + "='" + curVal + "';";
								eval(statement)
							} else {
								currentValue = element.getAttribute(curTarget);
								element.setAttribute(curTarget, currentValue + curVal)
							}
						}
					}
				} else {
					if (curTarget.charAt(0) == "$") {
						curTarget = curTarget.substring(1);
						if (curTarget == "innerHTML") {
							if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
								curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>")
							}
							element.innerHTML = element.innerHTML.replace("$", curVal)
						} else {
							if (curTarget == "textNode" || curTarget == "script") {
								if (nodeTag == "TEXTAREA") {
									curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r")
								}
								element.innerHTML = element.innerHTML.replace("$", curVal)
							} else {
								if (curTarget.indexOf("style.") == 0) {
									styleName = curTarget.substring(6, curTarget.length);
									statement = "element.style." + styleName + "='" + curVal + "';";
									eval(statement)
								} else {
									currentValue = element.getAttribute(curTarget);
									element.setAttribute(curTarget, currentValue.replace("$", curVal))
								}
							}
						}
					} else {
						if (INTERMediatorLib.isWidgetElement(element)) {
							element._im_setValue(curVal)
						} else {
							if (curTarget == "innerHTML") {
								if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
									curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br>")
								}
								element.innerHTML = curVal
							} else {
								if (curTarget == "textNode") {
									if (nodeTag == "TEXTAREA") {
										curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r")
									}
									textNode = document.createTextNode(curVal);
									element.appendChild(textNode)
								} else {
									if (curTarget == "script") {
										textNode = document.createTextNode(curVal);
										if (nodeTag == "SCRIPT") {
											element.appendChild(textNode)
										} else {
											scriptNode = document.createElement("script");
											scriptNode.type = "text/javascript";
											scriptNode.appendChild(textNode);
											element.appendChild(scriptNode)
										}
									} else {
										if (curTarget.indexOf("style.") == 0) {
											styleName = curTarget.substring(6, curTarget.length);
											statement = "element.style." + styleName + "='" + curVal + "';";
											eval(statement)
										} else {
											element.setAttribute(curTarget, curVal)
										}
									}
								}
							}
						}
					}
				}
			} else {
				if (INTERMediatorLib.isWidgetElement(element)) {
					element._im_setValue(curVal)
				} else {
					if (nodeTag == "INPUT") {
						typeAttr = element.getAttribute("type");
						if (typeAttr == "checkbox" || typeAttr == "radio") {
							valueAttr = element.value;
							curValues = curVal.split("\n");
							if (typeAttr == "checkbox" && curValues.length > 1) {
								element.checked = false;
								for (i = 0; i < curValues.length; i++) {
									if (valueAttr == curValues[i] && !INTERMediator.dontSelectRadioCheck) {
										if (INTERMediator.isIE) {
											element.setAttribute("checked", "checked")
										} else {
											element.checked = true
										}
									}
								}
							} else {
								if (valueAttr == curVal && !INTERMediator.dontSelectRadioCheck) {
									if (INTERMediator.isIE) {
										element.setAttribute("checked", "checked")
									} else {
										element.checked = true
									}
								} else {
									element.checked = false
								}
							}
						} else {
							element.value = curVal
						}
					} else {
						if (nodeTag == "SELECT") {
							needPostValueSet = true
						} else {
							if (INTERMediator.defaultTargetInnerHTML) {
								if (INTERMediator.isIE && nodeTag == "TEXTAREA") {
									curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r").replace(/\r/g, "<br/>")
								}
								element.innerHTML = curVal
							} else {
								if (nodeTag == "TEXTAREA") {
									curVal = curVal.replace(/\r\n/g, "\r").replace(/\n/g, "\r")
								}
								textNode = document.createTextNode(curVal);
								element.appendChild(textNode)
							}
						}
					}
				}
			}
			return needPostValueSet
		}

		function setupDeleteButton(encNodeTag, repNodeTag, endOfRepeaters, currentContext, keyField, keyValue, shouldDeleteNodes) {
			var buttonNode, thisId, deleteJSFunction, tdNodes, tdNode;
			if (currentContext["repeat-control"] && currentContext["repeat-control"].match(/delete/i)) {
				if (currentContext.relation || currentContext.records === undefined || currentContext.records > 1) {
					buttonNode = document.createElement("BUTTON");
					buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[6]));
					thisId = "IM_Button_" + buttonIdNum;
					buttonNode.setAttribute("id", thisId);
					buttonIdNum++;
					deleteJSFunction = function (a, b, c, d, e) {
						var contextName = a,
							keyField = b,
							keyValue = c,
							removeNodes = d,
							confirming = e;
						return function () {
							INTERMediator.deleteButton(contextName, keyField, keyValue, removeNodes, confirming)
						}
					};
					eventListenerPostAdding.push({
						id: thisId,
						event: "click",
						todo: deleteJSFunction(currentContext.name, keyField, keyValue, shouldDeleteNodes, currentContext["repeat-control"].match(/confirm-delete/i))
					});
					switch (encNodeTag) {
					case "TBODY":
						tdNodes = endOfRepeaters.getElementsByTagName("TD");
						tdNode = tdNodes[tdNodes.length - 1];
						tdNode.appendChild(buttonNode);
						break;
					case "UL":
					case "OL":
						endOfRepeaters.appendChild(buttonNode);
						break;
					case "DIV":
					case "SPAN":
						if (repNodeTag == "DIV" || repNodeTag == "SPAN") {
							endOfRepeaters.appendChild(buttonNode)
						}
						break
					}
				} else {
					deleteInsertOnNavi.push({
						kind: "DELETE",
						name: currentContext.name,
						key: keyField,
						value: keyValue,
						confirm: currentContext["repeat-control"].match(/confirm-delete/i)
					})
				}
			}
		}

		function setupInsertButton(currentContext, encNodeTag, repNodeTag, node, relationValue) {
			var buttonNode, shouldRemove, enclosedNode, footNode, trNode, tdNode, liNode, divNode, insertJSFunction;
			if (currentContext["repeat-control"] && currentContext["repeat-control"].match(/insert/i)) {
				if (relationValue || !currentContext.paging || currentContext.paging === false) {
					buttonNode = document.createElement("BUTTON");
					buttonNode.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[5]));
					shouldRemove = [];
					switch (encNodeTag) {
					case "TBODY":
						enclosedNode = node.parentNode;
						footNode = enclosedNode.getElementsByTagName("TFOOT")[0];
						if (footNode == null) {
							footNode = document.createElement("TFOOT");
							enclosedNode.appendChild(footNode)
						}
						trNode = document.createElement("TR");
						tdNode = document.createElement("TD");
						if (trNode.getAttribute("id") == null) {
							trNode.setAttribute("id", nextIdValue())
						}
						footNode.appendChild(trNode);
						trNode.appendChild(tdNode);
						tdNode.appendChild(buttonNode);
						shouldRemove = [trNode.getAttribute("id")];
						break;
					case "UL":
					case "OL":
						liNode = document.createElement("LI");
						liNode.appendChild(buttonNode);
						node.appendChild(liNode);
						break;
					case "DIV":
					case "SPAN":
						if (repNodeTag == "DIV" || repNodeTag == "SPAN") {
							divNode = document.createElement(repNodeTag);
							divNode.appendChild(buttonNode);
							node.appendChild(divNode)
						}
						break
					}
					insertJSFunction = function (a, b, c, d, e) {
						var contextName = a,
							relationValue = b,
							nodeId = c,
							removeNodes = d,
							confirming = e;
						return function () {
							INTERMediator.insertButton(contextName, relationValue, nodeId, removeNodes, confirming)
						}
					};
					INTERMediatorLib.addEvent(buttonNode, "click", insertJSFunction(currentContext.name, relationValue, node.getAttribute("id"), shouldRemove, currentContext["repeat-control"].match(/confirm-insert/i)))
				} else {
					deleteInsertOnNavi.push({
						kind: "INSERT",
						name: currentContext.name,
						key: currentContext.key ? currentContext.key : "id",
						confirm: currentContext["repeat-control"].match(/confirm-insert/i)
					})
				}
			}
		}

		function navigationSetup() {
			var navigation, i, insideNav, navLabel, node, start, pageSize, allCount, disableClass, prevPageCount, nextPageCount, endPageCount, onNaviInsertFunction, onNaviDeleteFunction;
			navigation = document.getElementById("IM_NAVIGATOR");
			if (navigation != null) {
				insideNav = navigation.childNodes;
				for (i = 0; i < insideNav.length; i++) {
					navigation.removeChild(insideNav[i])
				}
				navigation.innerHTML = "";
				navigation.setAttribute("class", "IM_NAV_panel");
				navLabel = INTERMediator.navigationLabel;
				if (navLabel == null || navLabel[8] !== false) {
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode(((navLabel == null || navLabel[8] == null) ? INTERMediatorOnPage.getMessages()[2] : navLabel[8])));
					node.setAttribute("class", "IM_NAV_button");
					INTERMediatorLib.addEvent(node, "click", function () {
						location.reload()
					})
				}
				if (navLabel == null || navLabel[4] !== false) {
					start = Number(INTERMediator.startFrom);
					pageSize = Number(INTERMediator.pagedSize);
					allCount = Number(INTERMediator.pagedAllCount);
					disableClass = " IM_NAV_disabled";
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode(((navLabel == null || navLabel[4] == null) ? INTERMediatorOnPage.getMessages()[1] : navLabel[4]) + (start + 1) + ((Math.min(start + pageSize, allCount) - start > 2) ? (((navLabel == null || navLabel[5] == null) ? "-" : navLabel[5]) + Math.min(start + pageSize, allCount)) : "") + ((navLabel == null || navLabel[6] == null) ? " / " : navLabel[6]) + (allCount) + ((navLabel == null || navLabel[7] == null) ? "" : navLabel[7])));
					node.setAttribute("class", "IM_NAV_info")
				}
				if (navLabel == null || navLabel[0] !== false) {
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode((navLabel == null || navLabel[0] == null) ? "<<" : navLabel[0]));
					node.setAttribute("class", "IM_NAV_button" + (start == 0 ? disableClass : ""));
					INTERMediatorLib.addEvent(node, "click", function () {
						INTERMediator.startFrom = 0;
						INTERMediator.constructMain(true)
					});
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode((navLabel == null || navLabel[1] == null) ? "<" : navLabel[1]));
					node.setAttribute("class", "IM_NAV_button" + (start == 0 ? disableClass : ""));
					prevPageCount = (start - pageSize > 0) ? start - pageSize : 0;
					INTERMediatorLib.addEvent(node, "click", function () {
						INTERMediator.startFrom = prevPageCount;
						INTERMediator.constructMain(true)
					});
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode((navLabel == null || navLabel[2] == null) ? ">" : navLabel[2]));
					node.setAttribute("class", "IM_NAV_button" + (start + pageSize >= allCount ? disableClass : ""));
					nextPageCount = (start + pageSize < allCount) ? start + pageSize : ((allCount - pageSize > 0) ? start : 0);
					INTERMediatorLib.addEvent(node, "click", function () {
						INTERMediator.startFrom = nextPageCount;
						INTERMediator.constructMain(true)
					});
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode((navLabel == null || navLabel[3] == null) ? ">>" : navLabel[3]));
					node.setAttribute("class", "IM_NAV_button" + (start + pageSize >= allCount ? disableClass : ""));
					endPageCount = allCount - pageSize;
					INTERMediatorLib.addEvent(node, "click", function () {
						INTERMediator.startFrom = (endPageCount > 0) ? endPageCount : 0;
						INTERMediator.constructMain(true)
					})
				}
				for (i = 0; i < deleteInsertOnNavi.length; i++) {
					switch (deleteInsertOnNavi[i]["kind"]) {
					case "INSERT":
						node = document.createElement("SPAN");
						navigation.appendChild(node);
						node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[3] + ": " + deleteInsertOnNavi[i]["name"]));
						node.setAttribute("class", "IM_NAV_button");
						onNaviInsertFunction = function (a, b, c) {
							var contextName = a,
								keyValue = b,
								confirming = c;
							return function () {
								INTERMediator.insertRecordFromNavi(contextName, keyValue, confirming)
							}
						};
						INTERMediatorLib.addEvent(node, "click", onNaviInsertFunction(deleteInsertOnNavi[i]["name"], deleteInsertOnNavi[i]["key"], deleteInsertOnNavi[i]["confirm"] ? true : false));
						break;
					case "DELETE":
						node = document.createElement("SPAN");
						navigation.appendChild(node);
						node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[4] + ": " + deleteInsertOnNavi[i]["name"]));
						node.setAttribute("class", "IM_NAV_button");
						onNaviDeleteFunction = function (a, b, c, d) {
							var contextName = a,
								keyName = b,
								keyValue = c,
								confirming = d;
							return function () {
								INTERMediator.deleteRecordFromNavi(contextName, keyName, keyValue, confirming)
							}
						};
						INTERMediatorLib.addEvent(node, "click", onNaviDeleteFunction(deleteInsertOnNavi[i]["name"], deleteInsertOnNavi[i]["key"], deleteInsertOnNavi[i]["value"], deleteInsertOnNavi[i]["confirm"] ? true : false));
						break
					}
				}
				if (INTERMediatorOnPage.getOptionsTransaction() == "none") {
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[7]));
					node.setAttribute("class", "IM_NAV_button");
					INTERMediatorLib.addEvent(node, "click", INTERMediator.saveRecordFromNavi)
				}
				if (INTERMediatorOnPage.requireAuthentication) {
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[8] + INTERMediatorOnPage.authUser));
					node.setAttribute("class", "IM_NAV_info");
					node = document.createElement("SPAN");
					navigation.appendChild(node);
					node.appendChild(document.createTextNode(INTERMediatorOnPage.getMessages()[9]));
					node.setAttribute("class", "IM_NAV_button");
					INTERMediatorLib.addEvent(node, "click", function () {
						INTERMediatorOnPage.logout();
						location.reload()
					})
				}
			}
		}

		function getEnclosedNode(rootNode, tableName, fieldName) {
			var i, j, nodeInfo, nInfo, children, r;
			if (rootNode.nodeType == 1) {
				nodeInfo = INTERMediatorLib.getLinkedElementInfo(rootNode);
				for (j = 0; j < nodeInfo.length; j++) {
					nInfo = INTERMediatorLib.getNodeInfoArray(nodeInfo[j]);
					if (nInfo.table == tableName && nInfo.field == fieldName) {
						return rootNode
					}
				}
			}
			children = rootNode.childNodes;
			for (i = 0; i < children.length; i++) {
				r = getEnclosedNode(children[i], tableName, fieldName);
				if (r !== null) {
					return r
				}
			}
			return null
		}

		function appendCredit() {
			var bodyNode, creditNode, cNode, spNode, aNode;
			if (document.getElementById("IM_CREDIT") == null) {
				bodyNode = document.getElementsByTagName("BODY")[0];
				creditNode = document.createElement("div");
				bodyNode.appendChild(creditNode);
				creditNode.setAttribute("id", "IM_CREDIT");
				creditNode.setAttribute("class", "IM_CREDIT");
				cNode = document.createElement("div");
				creditNode.appendChild(cNode);
				cNode.style.backgroundColor = "#F6F7FF";
				cNode.style.height = "2px";
				cNode = document.createElement("div");
				creditNode.appendChild(cNode);
				cNode.style.backgroundColor = "#EBF1FF";
				cNode.style.height = "2px";
				cNode = document.createElement("div");
				creditNode.appendChild(cNode);
				cNode.style.backgroundColor = "#E1EAFF";
				cNode.style.height = "2px";
				cNode = document.createElement("div");
				creditNode.appendChild(cNode);
				cNode.setAttribute("align", "right");
				cNode.style.backgroundColor = "#D7E4FF";
				cNode.style.padding = "2px";
				spNode = document.createElement("span");
				cNode.appendChild(spNode);
				cNode.style.color = "#666666";
				cNode.style.fontSize = "7pt";
				aNode = document.createElement("a");
				aNode.appendChild(document.createTextNode("INTER-Mediator"));
				aNode.setAttribute("href", "http://inter-mediator.info/");
				aNode.setAttribute("target", "_href");
				spNode.appendChild(document.createTextNode("Generated by "));
				spNode.appendChild(aNode);
				spNode.appendChild(document.createTextNode(" Ver.3.6(2013-07-05)"))
			}
		}
	}
};

function SHA1(e) {
	function d(A, z) {
		var y = (A << z) | (A >>> (32 - z));
		return y
	}

	function s(B) {
		var A = "";
		var y;
		var C;
		var z;
		for (y = 0; y <= 6; y += 2) {
			C = (B >>> (y * 4 + 4)) & 15;
			z = (B >>> (y * 4)) & 15;
			A += C.toString(16) + z.toString(16)
		}
		return A
	}

	function u(B) {
		var A = "";
		var z;
		var y;
		for (z = 7; z >= 0; z--) {
			y = (B >>> (z * 4)) & 15;
			A += y.toString(16)
		}
		return A
	}

	function b(z) {
		z = z.replace(/\r\n/g, "\n");
		var y = "";
		for (var B = 0; B < z.length; B++) {
			var A = z.charCodeAt(B);
			if (A < 128) {
				y += String.fromCharCode(A)
			} else {
				if ((A > 127) && (A < 2048)) {
					y += String.fromCharCode((A >> 6) | 192);
					y += String.fromCharCode((A & 63) | 128)
				} else {
					y += String.fromCharCode((A >> 12) | 224);
					y += String.fromCharCode(((A >> 6) & 63) | 128);
					y += String.fromCharCode((A & 63) | 128)
				}
			}
		}
		return y
	}
	var h;
	var w, v;
	var c = new Array(80);
	var n = 1732584193;
	var l = 4023233417;
	var k = 2562383102;
	var g = 271733878;
	var f = 3285377520;
	var t, r, q, p, o;
	var x;
	e = b(e);
	var a = e.length;
	var m = new Array();
	for (w = 0; w < a - 3; w += 4) {
		v = e.charCodeAt(w) << 24 | e.charCodeAt(w + 1) << 16 | e.charCodeAt(w + 2) << 8 | e.charCodeAt(w + 3);
		m.push(v)
	}
	switch (a % 4) {
	case 0:
		w = 2147483648;
		break;
	case 1:
		w = e.charCodeAt(a - 1) << 24 | 8388608;
		break;
	case 2:
		w = e.charCodeAt(a - 2) << 24 | e.charCodeAt(a - 1) << 16 | 32768;
		break;
	case 3:
		w = e.charCodeAt(a - 3) << 24 | e.charCodeAt(a - 2) << 16 | e.charCodeAt(a - 1) << 8 | 128;
		break
	}
	m.push(w);
	while ((m.length % 16) != 14) {
		m.push(0)
	}
	m.push(a >>> 29);
	m.push((a << 3) & 4294967295);
	for (h = 0; h < m.length; h += 16) {
		for (w = 0; w < 16; w++) {
			c[w] = m[h + w]
		}
		for (w = 16; w <= 79; w++) {
			c[w] = d(c[w - 3] ^ c[w - 8] ^ c[w - 14] ^ c[w - 16], 1)
		}
		t = n;
		r = l;
		q = k;
		p = g;
		o = f;
		for (w = 0; w <= 19; w++) {
			x = (d(t, 5) + ((r & q) | (~r & p)) + o + c[w] + 1518500249) & 4294967295;
			o = p;
			p = q;
			q = d(r, 30);
			r = t;
			t = x
		}
		for (w = 20; w <= 39; w++) {
			x = (d(t, 5) + (r ^ q ^ p) + o + c[w] + 1859775393) & 4294967295;
			o = p;
			p = q;
			q = d(r, 30);
			r = t;
			t = x
		}
		for (w = 40; w <= 59; w++) {
			x = (d(t, 5) + ((r & q) | (r & p) | (q & p)) + o + c[w] + 2400959708) & 4294967295;
			o = p;
			p = q;
			q = d(r, 30);
			r = t;
			t = x
		}
		for (w = 60; w <= 79; w++) {
			x = (d(t, 5) + (r ^ q ^ p) + o + c[w] + 3395469782) & 4294967295;
			o = p;
			p = q;
			q = d(r, 30);
			r = t;
			t = x
		}
		n = (n + t) & 4294967295;
		l = (l + r) & 4294967295;
		k = (k + q) & 4294967295;
		g = (g + p) & 4294967295;
		f = (f + o) & 4294967295
	}
	var x = u(n) + u(l) + u(k) + u(g) + u(f);
	return x.toLowerCase()
}(function () {
	var q = 8,
		t = "",
		r = 0,
		s = function (y) {
			var w = [],
				x = (1 << q) - 1,
				A = y.length * q,
				z;
			for (z = 0; z < A; z += q) {
				w[z >> 5] |= (y.charCodeAt(z / q) & x) << (32 - q - (z % 32))
			}
			return w
		}, d = function (x) {
			var w = [],
				A = x.length,
				z, y;
			for (z = 0; z < A; z += 2) {
				y = parseInt(x.substr(z, 2), 16);
				if (!isNaN(y)) {
					w[z >> 3] |= y << (24 - (4 * (z % 8)))
				} else {
					return "INVALID HEX STRING"
				}
			}
			return w
		}, g = function (x) {
			var w = (r) ? "0123456789ABCDEF" : "0123456789abcdef",
				B = "",
				A = x.length * 4,
				z, y;
			for (z = 0; z < A; z += 1) {
				y = x[z >> 2] >> ((3 - (z % 4)) * 8);
				B += w.charAt((y >> 4) & 15) + w.charAt(y & 15)
			}
			return B
		}, m = function (x) {
			var w = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
				C = "",
				A = x.length * 4,
				z, y, B;
			for (z = 0; z < A; z += 3) {
				B = (((x[z >> 2] >> 8 * (3 - z % 4)) & 255) << 16) | (((x[z + 1 >> 2] >> 8 * (3 - (z + 1) % 4)) & 255) << 8) | ((x[z + 2 >> 2] >> 8 * (3 - (z + 2) % 4)) & 255);
				for (y = 0; y < 4; y += 1) {
					if (z * 8 + y * 6 <= x.length * 32) {
						C += w.charAt((B >> 6 * (3 - y)) & 63)
					} else {
						C += t
					}
				}
			}
			return C
		}, h = function (w, y) {
			return (w >>> y) | (w << (32 - y))
		}, p = function (w, y) {
			return w >>> y
		}, k = function (w, B, A) {
			return (w & B) ^ (~w & A)
		}, b = function (w, B, A) {
			return (w & B) ^ (w & A) ^ (B & A)
		}, n = function (w) {
			return h(w, 2) ^ h(w, 13) ^ h(w, 22)
		}, l = function (w) {
			return h(w, 6) ^ h(w, 11) ^ h(w, 25)
		}, v = function (w) {
			return h(w, 7) ^ h(w, 18) ^ p(w, 3)
		}, u = function (w) {
			return h(w, 17) ^ h(w, 19) ^ p(w, 10)
		}, e = function (w, B) {
			var z = (w & 65535) + (B & 65535),
				A = (w >>> 16) + (B >>> 16) + (z >>> 16);
			return ((A & 65535) << 16) | (z & 65535)
		}, c = function (x, w, B, A) {
			var z = (x & 65535) + (w & 65535) + (B & 65535) + (A & 65535),
				y = (x >>> 16) + (w >>> 16) + (B >>> 16) + (A >>> 16) + (z >>> 16);
			return ((y & 65535) << 16) | (z & 65535)
		}, a = function (x, w, C, B, A) {
			var z = (x & 65535) + (w & 65535) + (C & 65535) + (B & 65535) + (A & 65535),
				y = (x >>> 16) + (w >>> 16) + (C >>> 16) + (B >>> 16) + (A >>> 16) + (z >>> 16);
			return ((y & 65535) << 16) | (z & 65535)
		}, f = function (F, E, D) {
			var R, Q, P, O, N, M, L, I, C, A, y, J, G, z, x, w = [],
				B;
			if (D === "SHA-224" || D === "SHA-256") {
				J = (((E + 65) >> 9) << 4) + 15;
				x = [1116352408, 1899447441, 3049323471, 3921009573, 961987163, 1508970993, 2453635748, 2870763221, 3624381080, 310598401, 607225278, 1426881987, 1925078388, 2162078206, 2614888103, 3248222580, 3835390401, 4022224774, 264347078, 604807628, 770255983, 1249150122, 1555081692, 1996064986, 2554220882, 2821834349, 2952996808, 3210313671, 3336571891, 3584528711, 113926993, 338241895, 666307205, 773529912, 1294757372, 1396182291, 1695183700, 1986661051, 2177026350, 2456956037, 2730485921, 2820302411, 3259730800, 3345764771, 3516065817, 3600352804, 4094571909, 275423344, 430227734, 506948616, 659060556, 883997877, 958139571, 1322822218, 1537002063, 1747873779, 1955562222, 2024104815, 2227730452, 2361852424, 2428436474, 2756734187, 3204031479, 3329325298];
				if (D === "SHA-224") {
					y = [3238371032, 914150663, 812702999, 4144912697, 4290775857, 1750603025, 1694076839, 3204075428]
				} else {
					y = [1779033703, 3144134277, 1013904242, 2773480762, 1359893119, 2600822924, 528734635, 1541459225]
				}
			}
			F[E >> 5] |= 128 << (24 - E % 32);
			F[J] = E;
			B = F.length;
			for (G = 0; G < B; G += 16) {
				R = y[0];
				Q = y[1];
				P = y[2];
				O = y[3];
				N = y[4];
				M = y[5];
				L = y[6];
				I = y[7];
				for (z = 0; z < 64; z += 1) {
					if (z < 16) {
						w[z] = F[z + G]
					} else {
						w[z] = c(u(w[z - 2]), w[z - 7], v(w[z - 15]), w[z - 16])
					}
					C = a(I, l(N), k(N, M, L), x[z], w[z]);
					A = e(n(R), b(R, Q, P));
					I = L;
					L = M;
					M = N;
					N = e(O, C);
					O = P;
					P = Q;
					Q = R;
					R = e(C, A)
				}
				y[0] = e(R, y[0]);
				y[1] = e(Q, y[1]);
				y[2] = e(P, y[2]);
				y[3] = e(O, y[3]);
				y[4] = e(N, y[4]);
				y[5] = e(M, y[5]);
				y[6] = e(L, y[6]);
				y[7] = e(I, y[7])
			}
			switch (D) {
			case "SHA-224":
				return [y[0], y[1], y[2], y[3], y[4], y[5], y[6]];
			case "SHA-256":
				return y;
			default:
				return []
			}
		}, o = function (x, w) {
			this.sha224 = null;
			this.sha256 = null;
			this.strBinLen = null;
			this.strToHash = null;
			if ("HEX" === w) {
				if (0 !== (x.length % 2)) {
					return "TEXT MUST BE IN BYTE INCREMENTS"
				}
				this.strBinLen = x.length * 4;
				this.strToHash = d(x)
			} else {
				if (("ASCII" === w) || ("undefined" === typeof (w))) {
					this.strBinLen = x.length * q;
					this.strToHash = s(x)
				} else {
					return "UNKNOWN TEXT INPUT TYPE"
				}
			}
		};
	o.prototype = {
		getHash: function (x, w) {
			var z = null,
				y = this.strToHash.slice();
			switch (w) {
			case "HEX":
				z = g;
				break;
			case "B64":
				z = m;
				break;
			default:
				return "FORMAT NOT RECOGNIZED"
			}
			switch (x) {
			case "SHA-224":
				if (null === this.sha224) {
					this.sha224 = f(y, this.strBinLen, x)
				}
				return z(this.sha224);
			case "SHA-256":
				if (null === this.sha256) {
					this.sha256 = f(y, this.strBinLen, x)
				}
				return z(this.sha256);
			default:
				return "HASH NOT RECOGNIZED"
			}
		},
		getHMAC: function (F, E, D, B) {
			var A, z, y, w, C, H, G = [],
				x = [];
			switch (B) {
			case "HEX":
				A = g;
				break;
			case "B64":
				A = m;
				break;
			default:
				return "FORMAT NOT RECOGNIZED"
			}
			switch (D) {
			case "SHA-224":
				H = 224;
				break;
			case "SHA-256":
				H = 256;
				break;
			default:
				return "HASH NOT RECOGNIZED"
			}
			if ("HEX" === E) {
				if (0 !== (F.length % 2)) {
					return "KEY MUST BE IN BYTE INCREMENTS"
				}
				z = d(F);
				C = F.length * 4
			} else {
				if ("ASCII" === E) {
					z = s(F);
					C = F.length * q
				} else {
					return "UNKNOWN KEY INPUT TYPE"
				}
			} if (64 < (C / 8)) {
				z = f(z, C, D);
				z[15] &= 4294967040
			} else {
				if (64 > (C / 8)) {
					z[15] &= 4294967040
				}
			}
			for (y = 0; y <= 15; y += 1) {
				G[y] = z[y] ^ 909522486;
				x[y] = z[y] ^ 1549556828
			}
			w = f(G.concat(this.strToHash), 512 + this.strBinLen, D);
			w = f(x.concat(w), 512 + H, D);
			return (A(w))
		}
	};
	window.jsSHA = o
}());
var biRadixBase = 2;
var biRadixBits = 16;
biRadixBits = biRadixBits - biRadixBits % 4;
var bitsPerDigit = biRadixBits;
var biRadix = 1 << biRadixBits;
var biHalfRadix = biRadix >>> 1;
var biRadixSquared = biRadix * biRadix;
var maxDigitVal = biRadix - 1;
var maxInteger = 4294967295;
var biHexPerDigit = biRadixBits / 4;
var bigZero = biFromNumber(0);
var bigOne = biFromNumber(1);
var dpl10 = 9;
var lr10 = biFromNumber(1000000000);

function BigInt(a) {
	this.isNeg = false;
	if (a == -1) {
		return
	}
	if (a) {
		this.digits = new Array(a);
		while (a) {
			this.digits[--a] = 0
		}
	} else {
		this.digits = [0]
	}
}
BigInt.prototype.isZero = function () {
	return this.digits[0] == 0 && biNormalize(this).digits.length == 1
};
BigInt.prototype.isOne = function () {
	return this.digits[0] == 1 && !this.isNeg && biNormalize(this).digits.length == 1
};
BigInt.prototype.isEqual = function (b) {
	if (this.isNeg != b.isNeg) {
		return false
	}
	if (this.digits.length != b.digits.length) {
		return false
	}
	for (var a = this.digits.length - 1; a > -1; a--) {
		if (this.digits[a] != b.digits[a]) {
			return false
		}
	}
	return true
};
BigInt.prototype.blankZero = function () {
	this.digits.length = 1;
	this.digits[0] = 0
};
BigInt.prototype.blankOne = function () {
	this.digits.length = 1;
	this.digits[0] = 1
};
BigInt.prototype.blankEmpty = function () {
	this.digits.length = 0
};

function biCopy(b) {
	var a = new BigInt(-1);
	a.digits = b.digits.slice(0);
	a.isNeg = b.isNeg;
	return a
}

function biAbs(b) {
	var a = new BigInt(-1);
	a.digits = b.digits.slice(0);
	a.isNeg = false;
	return a
}

function biMinus(b) {
	var a = new BigInt(-1);
	a.digits = b.digits.slice(0);
	a.isNeg = !b.isNeg;
	return a
}

function biFromNumber(c) {
	if (Math.abs(c) > maxInteger) {
		return (biFromFloat(c))
	}
	var a = new BigInt();
	if (a.isNeg = c < 0) {
		c = -c
	}
	var b = 0;
	while (c > 0) {
		a.digits[b++] = c & maxDigitVal;
		c >>>= biRadixBits
	}
	return a
}

function biFromFloat(d) {
	var a = new BigInt();
	if (a.isNeg = d < 0) {
		d = -d
	}
	var b = 0;
	while (d > 0) {
		var e = Math.floor(d / biRadix);
		a.digits[b++] = d - e * biRadix;
		d = e
	}
	return a
}

function biFromString(l, k) {
	if (k == 16) {
		return biFromHex(l)
	}
	var a = l.charAt(0) == "-";
	var e = (a ? 1 : 0) - 1;
	var m = new BigInt();
	var b = biCopy(bigOne);
	for (var d = l.length - 1; d > e; d--) {
		var f = l.charCodeAt(d);
		var g = charToHex(f);
		var h = biMultiplyDigit(b, g);
		m = biAdd(m, h);
		b = biMultiplyDigit(b, k)
	}
	m.isNeg = a;
	return biNormalize(m)
}

function biFromDecimal(a) {
	return biFromString(a, 10)
}

function biFromHex(e) {
	var b = new BigInt();
	if (e.charAt(0) == "-") {
		b.isNeg = true;
		e = substr(e, 1)
	} else {
		b.isNeg = false
	}
	var a = e.length;
	for (var d = a, c = 0; d > 0; d -= biHexPerDigit, c++) {
		b.digits[c] = hexToDigit(e.substr(Math.max(d - biHexPerDigit, 0), Math.min(d, biHexPerDigit)))
	}
	return biNormalize(b)
}

function reverseStr(c) {
	var a = "";
	for (var b = c.length - 1; b > -1; b--) {
		a += c.charAt(b)
	}
	return a
}
var hexatrigesimalToChar = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");

function biToString(d, f) {
	if (f == 16) {
		return biToHex(d)
	}
	var c = biFromNumber(f);
	var e = biDivideModulo(biAbs(d), c);
	var a = hexatrigesimalToChar[e[1].digits[0]];
	while (!e[0].isZero()) {
		e = biDivideModulo(e[0], c);
		a += hexatrigesimalToChar[e[1].digits[0]]
	}
	return (d.isNeg ? "-" : "") + reverseStr(a)
}

function biToDecimal(a) {
	return biToString(a, 10)
}
var hexToChar = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");

function digitToHex(d) {
	var b = 15;
	var a = "";
	for (var c = 0; c < biHexPerDigit; c++) {
		a += hexToChar[d & b];
		d >>>= 4
	}
	return reverseStr(a)
}

function digitToHexTrunk(d) {
	if (d == 0) {
		return "0"
	}
	var b = 15;
	var a = "";
	for (var c = 0; c < biHexPerDigit && d > 0; c++) {
		a += hexToChar[d & b];
		d >>>= 4
	}
	return reverseStr(a)
}

function biToHex(b) {
	var a = b.isNeg ? "-" : "";
	var c = biHighIndex(b);
	a += digitToHexTrunk(b.digits[c--]);
	while (c > -1) {
		a += digitToHex(b.digits[c--])
	}
	return a
}

function biToNumber(b) {
	var a = 0;
	var d = 1;
	var c = biHighIndex(b) + 1;
	for (var e = 0; e < c; e++) {
		a += b.digits[e] * d;
		d *= biRadix
	}
	return b.isNeg ? -a : a
}

function charToHex(k) {
	var d = 48;
	var b = d + 9;
	var e = 97;
	var h = e + 25;
	var g = 65;
	var f = 65 + 25;
	var a;
	if (k >= d && k <= b) {
		a = k - d
	} else {
		if (k >= g && k <= f) {
			a = 10 + k - g
		} else {
			if (k >= e && k <= h) {
				a = 10 + k - e
			} else {
				a = 0
			}
		}
	}
	return a
}

function hexToDigit(d) {
	var b = 0;
	var a = Math.min(d.length, biHexPerDigit);
	for (var c = 0; c < a; c++) {
		b <<= 4;
		b |= charToHex(d.charCodeAt(c))
	}
	return b
}

function biDump(a) {
	return (a.isNeg ? "minus " : "plus ") + a.digits.join(" ")
}

function biNormalize(a) {
	var b = a.digits.length;
	if (a.digits[b - 1] != 0 && !isNaN(a.digits[b - 1])) {
		return a
	}
	for (var c = b - 1; c > 0; c--) {
		if (a.digits[c] == 0 || isNaN(a.digits[c])) {
			a.digits.pop()
		} else {
			return a
		}
	}
	if (a.digits.length == 1 && a.digits[0] == 0) {
		a.isNeg = false
	}
	if (isNaN(a.digits[0])) {
		throw new Error("Undefined BigInt: " + biDump(a))
	}
	return a
}

function biHighIndex(a) {
	biNormalize(a);
	return a.digits.length - 1
}

function biNumBits(c) {
	var f = biHighIndex(c);
	var e = c.digits[f];
	var b = (f + 1) * bitsPerDigit;
	var a;
	for (a = b; a > b - bitsPerDigit; a--) {
		if ((e & biHalfRadix) != 0) {
			break
		}
		e <<= 1
	}
	return a
}

function biCompareAbs(b, e) {
	var a = biHighIndex(b);
	var d = biHighIndex(e);
	if (a != d) {
		return 1 - 2 * ((a < d) ? 1 : 0)
	}
	for (var c = b.digits.length - 1; c > -1; c--) {
		if (b.digits[c] != e.digits[c]) {
			return 1 - 2 * ((b.digits[c] < e.digits[c]) ? 1 : 0)
		}
	}
	return 0
}

function biCompare(a, b) {
	if (a.isNeg != b.isNeg) {
		return 1 - 2 * (a.isNeg ? 1 : 0)
	}
	return a.isNeg ? -biCompareAbs(a, b) : biCompareAbs(a, b)
}

function biAddNatural(l, h) {
	var f = biHighIndex(l) + 1;
	var e = biHighIndex(h) + 1;
	var d = 0;
	var g = 0;
	if (f > e) {
		var m = biAbs(l);
		var a = h;
		var b = e
	} else {
		var m = biAbs(h);
		var a = l;
		var b = f
	}
	while (d < b) {
		m.digits[d] += a.digits[d] + g;
		if (m.digits[d] < biRadix) {
			g = 0
		} else {
			m.digits[d] &= maxDigitVal;
			g = 1
		}
		d++
	}
	while (g > 0) {
		m.digits[d] = (m.digits[d] || 0) + g;
		if (m.digits[d] < biRadix) {
			g = 0
		} else {
			m.digits[d] &= maxDigitVal;
			g = 1
		}
		d++
	}
	return m
}

function biSubtractNatural(k, h) {
	var f = biHighIndex(k) + 1;
	var e = biHighIndex(h) + 1;
	var m = biAbs(k);
	var a = m.digits;
	var b = k.digits;
	var l = h.digits;
	var g = 0;
	for (var d = 0; d < e; d++) {
		if (b[d] >= l[d] - g) {
			a[d] = b[d] - l[d] + g;
			g = 0
		} else {
			a[d] = biRadix + b[d] - l[d] + g;
			g = -1
		}
	}
	while (g < 0 && d < f) {
		if (b[d] >= -g) {
			a[d] = b[d] + g;
			g = 0
		} else {
			a[d] = biRadix + b[d] + g;
			g = -1
		}
		d++
	}
	return biNormalize(m)
}

function biAdd(b, d) {
	var a;
	if (!b.isNeg && !d.isNeg) {
		return biAddNatural(b, d)
	}
	if (b.isNeg && d.isNeg) {
		a = biAddNatural(b, d);
		a.isNeg = true;
		return a
	}
	var c = biCompareAbs(b, d);
	if (c == 0) {
		return biFromNumber(0)
	}
	if (c > 0) {
		a = biSubtractNatural(b, d);
		a.isNeg = b.isNeg
	}
	if (c < 0) {
		a = biSubtractNatural(d, b);
		a.isNeg = d.isNeg
	}
	return a
}

function biSubtract(b, d) {
	var a;
	if (!b.isNeg && d.isNeg) {
		return biAddNatural(b, d)
	}
	if (b.isNeg && !d.isNeg) {
		a = biAddNatural(b, d);
		a.isNeg = true;
		return a
	}
	var c = biCompareAbs(b, d);
	if (c == 0) {
		return biCopy(bigZero)
	}
	if (c > 0) {
		a = biSubtractNatural(b, d);
		a.isNeg = b.isNeg
	}
	if (c < 0) {
		a = biSubtractNatural(d, b);
		a.isNeg = !b.isNeg
	}
	return a
}

function biMultiply(o, m) {
	var l, q, d, f;
	var e = biHighIndex(o) + 1;
	var r = biHighIndex(m) + 1;
	if (e == 1 && o.digits[0] == 0 || r == 1 && m.digits[0] == 0) {
		return new BigInt()
	}
	var s = new BigInt(e + r);
	var a = s.digits;
	var b = o.digits;
	var p = m.digits;
	for (var h = 0; h < r; h++) {
		l = 0;
		f = h;
		for (var g = 0; g < e; g++, f++) {
			d = a[f] + b[g] * p[h] + l;
			a[f] = d & maxDigitVal;
			l = d >>> biRadixBits
		}
		a[h + e] = l
	}
	s.isNeg = o.isNeg != m.isNeg;
	return biNormalize(s)
}

function biMultiplyDigit(b, h) {
	var g = biHighIndex(b) + 1;
	var a = new BigInt(g);
	var f = 0;
	for (var d = 0; d < g; d++) {
		var e = a.digits[d] + b.digits[d] * h + f;
		a.digits[d] = e & maxDigitVal;
		f = e >>> biRadixBits
	}
	a.digits[g] = f;
	return a
}

function arrayCopy(f, h, c, g, e) {
	if (h >= f.length) {
		if (c.length == 0) {
			c[0] = 0
		}
		return
	}
	for (var d = 0; d < g; d++) {
		c[d] = 0
	}
	var a = Math.min(h + e, f.length);
	for (var d = h, b = g; d < a; d++, b++) {
		c[b] = f[d]
	}
}

function biShiftLeft(b, g) {
	var d = Math.floor(g / bitsPerDigit);
	var a = new BigInt();
	arrayCopy(b.digits, 0, a.digits, d, b.digits.length);
	var f = g % bitsPerDigit;
	var c = bitsPerDigit - f;
	a.digits[a.digits.length] = a.digits[a.digits.length] >>> c;
	for (var e = a.digits.length - 1; e > 0; e--) {
		a.digits[e] = ((a.digits[e] << f) & maxDigitVal) | (a.digits[e - 1] >>> c)
	}
	a.digits[0] = (a.digits[0] << f) & maxDigitVal;
	a.isNeg = b.isNeg;
	return biNormalize(a)
}

function biShiftRight(b, g) {
	var c = Math.floor(g / bitsPerDigit);
	var a = new BigInt();
	arrayCopy(b.digits, c, a.digits, 0, b.digits.length - c);
	var e = g % bitsPerDigit;
	var f = bitsPerDigit - e;
	for (var d = 0; d < a.digits.length - 1; d++) {
		a.digits[d] = (a.digits[d] >>> e) | ((a.digits[d + 1] << f) & maxDigitVal)
	}
	a.digits[a.digits.length - 1] >>>= e;
	a.isNeg = b.isNeg;
	return biNormalize(a)
}

function biMultiplyByRadixPower(b, c) {
	var a = new BigInt();
	arrayCopy(b.digits, 0, a.digits, c, b.digits.length);
	return a
}

function biDivideByRadixPower(b, c) {
	var a = new BigInt();
	arrayCopy(b.digits, c, a.digits, 0, b.digits.length - c);
	return a
}

function biModuloByRadixPower(b, c) {
	var a = new BigInt();
	arrayCopy(b.digits, 0, a.digits, 0, c);
	return a
}

function biMultiplyModByRadixPower(o, m, d) {
	var l, r, e, g;
	var f = biHighIndex(o) + 1;
	var s = biHighIndex(m) + 1;
	if (f == 1 && o.digits[0] == 0 || s == 1 && m.digits[0] == 0) {
		return new BigInt()
	}
	var v = new BigInt(d);
	var a = v.digits;
	var b = o.digits;
	var q = m.digits;
	for (var h = 0; h < s && h < d; h++) {
		l = 0;
		g = h;
		for (j = 0; j < f && g < d; j++, g++) {
			e = a[g] + b[j] * q[h] + l;
			a[g] = e & maxDigitVal;
			l = e >>> biRadixBits
		}
		a[h + f] = l
	}
	v = biModuloByRadixPower(v, d);
	v.isNeg = o.isNeg != m.isNeg;
	return biNormalize(v)
}

function biDivideModuloNatural(m, k) {
	var b, o, d, n, h;
	var g = biHighIndex(m);
	var f = biHighIndex(k);
	var c = new BigInt(-1);
	c.digits = [];
	var a = new BigInt();
	for (var e = g; e > -1; e--) {
		a.digits.unshift(m.digits[e]);
		h = biCompareAbs(k, a);
		if (h > 0) {
			c.digits.unshift(0);
			continue
		}
		if (h == 0) {
			c.digits.unshift(1);
			a.blankZero();
			continue
		}
		var l = biHighIndex(a);
		if (l == f) {
			d = Math.floor((a.digits[l] * biRadix + (a.digits[l - 1] || 0)) / (k.digits[f] * biRadix + (k.digits[f - 1] || 0) + 1))
		} else {
			d = Math.floor((a.digits[l] * biRadixSquared + (a.digits[l - 1] || 0) * biRadix + (a.digits[l - 2] || 0)) / (k.digits[f] * biRadix + (k.digits[f - 1] || 0) + 1))
		}
		d = Math.max(0, Math.min(d, maxDigitVal));
		n = biMultiplyDigit(k, d);
		a = biSubtract(a, n);
		if (a.isNeg) {
			while (a.isNeg) {
				a = biAdd(a, k);
				d--
			}
		} else {
			while (biCompare(a, k) >= 0) {
				a = biSubtract(a, k);
				d++
			}
		}
		c.digits.unshift(d)
	}
	return [biNormalize(c), biNormalize(a)]
}

function biDivideModulo(c, g) {
	var f, e;
	if (biCompareAbs(c, g) < 0) {
		if ((c.isNeg && g.isNeg) || (!c.isNeg && !g.isNeg)) {
			f = biFromNumber(0);
			e = biCopy(c)
		} else {
			f = biFromNumber(-1);
			e = biAdd(g, c)
		}
		return [f, e]
	}
	var d = c.isNeg;
	var b = g.isNeg;
	var a = biDivideModuloNatural(biAbs(c), biAbs(g));
	f = a[0];
	e = a[1];
	if (!d && !b) {
		return [f, e]
	} else {
		if (d && b) {
			e.isNeg = true;
			return [f, e]
		} else {
			f.isNeg = true;
			f = biSubtract(f, bigOne);
			e.isNeg = d;
			e = biAdd(e, g)
		}
	} if (e.digits[0] == 0 && biHighIndex(e) == 0) {
		e.isNeg = false
	}
	return [f, e]
}

function biDivide(a, b) {
	return biDivideModulo(a, b)[0]
}

function biModulo(a, b) {
	return biDivideModulo(a, b)[1]
}

function biMultiplyMod(b, c, a) {
	return biModulo(biMultiply(b, c), a)
}

function biPow(c, e) {
	var b = biCopy(bigOne);
	var d = c;
	while (true) {
		if ((e & 1) != 0) {
			b = biMultiply(b, d)
		}
		e >>>= 1;
		if (e == 0) {
			break
		}
		d = biMultiply(d, d)
	}
	return b
}

function biPowMod(d, g, c) {
	var b = biCopy(bigOne);
	var e = d;
	var f = g;
	while (true) {
		if ((f.digits[0] & 1) != 0) {
			b = biMultiplyMod(b, e, c)
		}
		f = biShiftRight(f, 1);
		if (f.digits[0] == 0 && biHighIndex(f) == 0) {
			break
		}
		e = biMultiplyMod(e, e, c)
	}
	return b
}

function biRandom(b) {
	var a = new BigInt();
	while (b--) {
		a.digits[b] = Math.floor(Math.random() * maxDigitVal)
	}
	return a
}

function biModularInverse(c, b) {
	c = biModulo(c, b);
	var a = biExtendedEuclid(b, c);
	if (!a[2].isOne()) {
		return null
	}
	return biModulo(a[1], b)
}

function biExtendedEuclid(e, d) {
	if (biCompare(e, d) >= 0) {
		return biExtendedEuclidNatural(e, d)
	}
	var c = biExtendedEuclidNatural(d, e);
	return [c[1], c[0], c[2]]
}

function biExtendedEuclidNatural(o, l) {
	var k, d, c, f, e, n, h, m, g;
	if (l.isZero()) {
		return [biFromNumber(1), biFromNumber(0), o]
	}
	f = biFromNumber(0);
	e = biFromNumber(1);
	n = biFromNumber(1);
	h = biFromNumber(0);
	while (!l.isZero()) {
		k = biDivideModulo(o, l);
		d = k[0];
		c = k[1];
		m = biSubtract(e, biMultiply(d, f));
		g = biSubtract(h, biMultiply(d, n));
		o = l;
		l = c;
		e = f;
		f = m;
		h = n;
		n = g
	}
	return [e, h, o]
}

function biMontgomeryPowMod(c, e, f) {
	var b = biFromNumber(1);
	var a = biModulo(biMultiply(c, f.R), f);
	for (var d = e.bin.length - 1; d > -1; d--) {
		if (e.bin.charAt(d) == "1") {
			b = biMultiply(b, a);
			b = biMontgomeryModulo(b, f)
		}
		a = biMultiply(a, a);
		a = biMontgomeryModulo(a, f)
	}
	return b
}

function biMontgomeryModulo(b, c) {
	var a = biModuloByRadixPower(b, c.nN);
	a = biMultiplyModByRadixPower(a, c.Ninv, c.nN);
	a = biMultiply(a, c);
	a = biAdd(b, a);
	a = biDivideByRadixPower(a, c.nN);
	while (biCompare(a, c) >= 0) {
		a = biSubtract(a, c)
	}
	return a
}

function biRSAKeyPair(b, c, a) {
	this.e = biFromHex(b);
	this.d = biFromHex(c);
	this.m = biFromHex(a);
	this.chunkSize = 2 * biHighIndex(this.m);
	this.radix = 16;
	this.m.nN = biHighIndex(this.m) + 1;
	this.m.R = biMultiplyByRadixPower(biFromNumber(1), this.m.nN);
	this.m.EGCD = biExtendedEuclid(this.m.R, this.m);
	this.m.Ri = this.m.EGCD[0];
	this.m.Rinv = biModulo(this.m.EGCD[0], this.m);
	this.m.Ni = biMinus(this.m.EGCD[1]);
	this.m.Ninv = biModulo(biMinus(this.m.EGCD[1]), this.m.R);
	this.e.bin = biToString(this.e, 2);
	this.d.bin = biToString(this.d, 2)
}
biRSAKeyPair.prototype.biEncryptedString = biEncryptedString;
biRSAKeyPair.prototype.biDecryptedString = biDecryptedString;

function biEncryptedString(h) {
	h = biUTF8Encode(h);
	h = h.replace(/[\x00]/gm, String.fromCharCode(255));
	h = h + String.fromCharCode(254);
	var a = h.length;
	h = h + biRandomPadding(this.chunkSize - a % this.chunkSize);
	var a = h.length;
	var l = "";
	var e, d, c, b;
	b = new BigInt();
	for (var e = 0; e < a; e += this.chunkSize) {
		b.blankZero();
		d = 0;
		for (c = e; c < e + this.chunkSize && c < a; ++d) {
			b.digits[d] = h.charCodeAt(c++);
			b.digits[d] += (h.charCodeAt(c++) || 0) << 8
		}
		var g = biMontgomeryPowMod(b, this.e, this.m);
		var f = biToHex(g);
		l += f + ","
	}
	return l.substring(0, l.length - 1)
}

function biDecryptedString(e) {
	var g = e.split(",");
	var a = "";
	var d, c, f;
	for (d = 0; d < g.length; ++d) {
		var b;
		b = biFromHex(g[d], 10);
		f = biMontgomeryPowMod(b, this.d, this.m);
		for (c = 0; c <= biHighIndex(f); ++c) {
			a += String.fromCharCode(f.digits[c] & 255, f.digits[c] >> 8)
		}
	}
	a = a.replace(/\xff/gm, String.fromCharCode(0));
	a = a.substr(0, a.lastIndexOf(String.fromCharCode(254)));
	return biUTF8Decode(a)
}

function biUTF8Encode(d) {
	var b = "";
	var a = d.length;
	for (var f = 0; f < a; f++) {
		var e = d.charCodeAt(f);
		if (e < 128) {
			b += String.fromCharCode(e)
		} else {
			if ((e > 127) && (e < 2048)) {
				b += String.fromCharCode((e >> 6) | 192);
				b += String.fromCharCode((e & 63) | 128)
			} else {
				b += String.fromCharCode((e >> 12) | 224);
				b += String.fromCharCode(((e >> 6) & 63) | 128);
				b += String.fromCharCode((e & 63) | 128)
			}
		}
	}
	return b
}

function biUTF8Decode(e) {
	var d = "";
	var a = e.length;
	var b;
	for (var g = 0; g < a; g++) {
		var f = e.charCodeAt(g);
		if (f < 128) {
			d += String.fromCharCode(f);
			b = 0
		} else {
			if ((f > 191) && (f < 224)) {
				b = ((f & 31) << 6);
				f = e.charCodeAt(++g);
				b += (f & 63);
				d += String.fromCharCode(b)
			} else {
				b = ((f & 15) << 12);
				f = e.charCodeAt(++g);
				b += ((f & 63) << 6);
				f = e.charCodeAt(++g);
				b += (f & 63);
				d += String.fromCharCode(b)
			}
		}
	}
	return d
}

function biRandomPadding(c) {
	var a = "";
	for (var b = 0; b < c; b++) {
		a = a + String.fromCharCode(Math.floor(Math.random() * 126) + 1)
	}
	return a
};
//依存文法连线中的元素
var DpElement = (function(){

	function _dpElement(pos){
		var _this = this;

		if( _this instanceof _dpElement )
		{
			_this.pos = pos;

			_this.connections = [];
			_this.rightConnections = [];
			_this.leftConnections = [];

			_this.leftStubs = [];
			_this.rightStubs = [];


			/**
			 * @param dpElements 当前所有连线的元素数组
			 * @param dpConnection 当前连线
			 */
			_this.addLeftConnection = function(dpConnection, dpElements){
				_this.leftConnections.push(dpConnection);
				_this.connections.push(dpConnection);
				_this.update(dpConnection, dpElements);
			};

			/**
			 * @param dpElements 当前所有连线的元素数组
			 * @param dpConnection 当前连线
			 */
			_this.addRightConnection = function(dpConnection, dpElements){
				_this.rightConnections.push(dpConnection);
				_this.connections.push(dpConnection);
				_this.update(dpConnection, dpElements);
			};
			
			/**
			 * @param dpElements 当前所有连线的元素数组
			 * @param dpConnection 当前连线
			 */
			_this.update = function(dpConnection, dpElements){
				
				//find the section between the dpConnection
				var leftPos, rightPos

				if( dpConnection.sourceElement.pos < dpConnection.targetElement.pos )
				{
					leftPos = dpConnection.sourceElement.pos
					rightPos = dpConnection.targetElement.pos
				}
				else
				{
					leftPos = dpConnection.sourceElement.pos
					rightPos = dpConnection.targetElement.pos
				}

				//assign connection stub
				var stubs = [];
				for (var s = leftPos; s <= rightPos; s++) 
				{
					if(!dpElements.hasOwnProperty(s))
						continue;

					if(s == leftPos)
					{
						stubs = stubs.concat(dpElements[s].rightStubs);
					}
					else if(s == rightPos)
					{
						stubs = stubs.concat(dpElements[s].leftStubs);
					}
					else
					{
						stubs = stubs.concat(dpElements[s].leftStubs, dpElements[s].rightStubs);	
					}
				}

				var sl = stubs.length;
				for (var i = 1; i < sl; i++)
				{
					if( $.inArray(i, stubs) == -1 )
					{
						dpConnection.stub = i;
						break;
					}
				}

				if(!dpConnection.stub)
				{
					if( stubs.length > 0)
						dpConnection.stub = Math.max.apply(null, stubs) + 1;//"current max stub" + 1
					else
						dpConnection.stub = 1;//empty stubs
				}

				//assign current dpConnection elements leftStubs and rightStubs
				if( dpConnection.sourceElement.pos < dpConnection.targetElement.pos )
				{
					dpConnection.sourceElement.rightStubs.push( dpConnection.stub );
					dpConnection.targetElement.leftStubs.push( dpConnection.stub );
				}
				else
				{
					dpConnection.sourceElement.leftStubs.push( dpConnection.stub );
					dpConnection.targetElement.rightStubs.push( dpConnection.stub );
				}
			};

			
		}
		else
		{
			return new _dpElement(pos);
		}

	}

	_dpElement.prototype ={
		constructor: _dpElement
	}

	return _dpElement;
})();

//依存文法的连线
var DpConnection = (function(){

	function _dpConnection(source, target){
		var _this = this;

		if( _this instanceof _dpConnection )
		{
			_this.sourceElement = source;
			_this.targetElement = target;
			_this.stub = 0;

		}
		else
		{
			return new _dpConnection(source, target);
		}

	}

	_dpConnection.prototype ={
		constructor: _dpConnection
	}

	return _dpConnection;
})();

//依存文法分析
var Dp = (function(){

	function _dp(_options){
		var _this = this;

		if(_this instanceof _dp)
		{
			_this.words = _options.words;
			_this.plumb = _options.plumb;
			_this.container = _options.container;

			_this.left = _options.left;
			_this.margin = _options.margin;

			_this.targetAnchors = _options.targetAnchors;
			_this.sourceAnchors = _options.sourceAnchors;

			// drawFlowChart BEGIN
			_this.drawFlowChart = function (){
				var dpHtml = '<div class="dp-item" id="dp--1">Root</div>',
					connLog = {},
					dpElements = [],
					dpConnections = [];

				console.log("json data construct  "+JSON.stringify(_this.words));
				//add root element to the array
				dpElements.push( new DpElement(-1) );

				for (var _k in _this.words)
                {
                    dpHtml += "<div class='dp-item' id='dp-"+ _k +"'>" + _this.words[_k].content + "</div>"

                    //init connLog  array and dpElements array
                    if(!connLog[_k])
                    	connLog[_k] = [];

                   	connLog[_k][0] = _this.words[_k].head
                   	connLog[_k][1] = _k
                   	
                   	dpElements.push( new DpElement(_k) );
                }

                //console.log("connLog " + JSON.stringify(connLog));

                $("#" + _this.container).html(dpHtml)
                $('#dp--1').css('left', _this.left);

                var sourceK, targetK, sourceE, targetE, dpConn
                for (var _k in _this.words)
                {
                    $('#dp-'+_k).css('left', _this.margin * (parseInt(_k) + 1));

                    //connect elements BEGIN
                    sourceK = connLog[_k][0]
                    targetK = connLog[_k][1]

                    //correct root index not exists the dpElements array
                    if(sourceK == -1)
                    {
                    	sourceK = 0;	
                    }

                    sourceE = dpElements[sourceK]
                    targetE = dpElements[targetK]

                    dpConn = new DpConnection(sourceE, targetE)
                    dpConnections.push(dpConn)

                    //console.log("  typeof connLog[_k] " + JSON.stringify( connLog[_k]) );
                    //console.log( " dpElements[targetK] " + JSON.stringify(dpElements) + "   connlog " + connLog[_k] + "  targetK  " + targetK);

                    if(sourceK < targetK)
                    {
	                    dpElements[sourceK].addRightConnection(dpConn, dpElements);
	                    dpElements[targetK].addLeftConnection(dpConn, dpElements);
	                }
	                else
                    {
	                    dpElements[sourceK].addLeftConnection(dpConn, dpElements);
	                    dpElements[targetK].addRightConnection(dpConn, dpElements);
	                }
	                //connect elements END
                }

                var instance = _this.plumb.getInstance({
                    // the overlays to decorate each connection with.  note that the label overlay uses a function to generate the label text; in this
                    // case it returns the 'labelText' member that we set on each connection in the 'init' method below.
                    ConnectionOverlays: [
                        [ "Arrow", {
                            location: 1,
                            visible:true,
                            width:11,
                            length:11,
                            id:"ARROW",
                            events:{
                                click:function() { console.log("you clicked on the arrow overlay")}
                            }
                        } ],
                        [ "Label", {
                            location: 0.8,
                            id: "label",
                            cssClass: "aLabel",
                            events:{
                                tap:function() { console.log("hey"); }
                            }
                        }]
                    ],
                    Container: _this.container
                });

                // this is the paint style for the connecting lines..
                var connectorPaintStyle = {
                        strokeWidth: 1,
                        stroke: "#61B7CF",
                        joinstyle: "round",
                        outlineStroke: "white",
                        outlineWidth: 2
                    },
                // .. and this is the hover style.
                connectorHoverStyle = {
                    strokeWidth: 3,
                    stroke: "#216477",
                    outlineWidth: 5,
                    outlineStroke: "white"
                },
                endpointHoverStyle = {
                    fill: "#216477",
                    stroke: "#216477"
                },
                // the definition of source endpoints (the small blue ones)
                sourceEndpoint = {
                    endpoint: "Dot",
                    paintStyle: {
                        stroke: "#7AB02C",
                        fill: "transparent",
                        radius: 1,
                        strokeWidth: 1
                    },
                    isSource: true,
                    connectorStyle: connectorPaintStyle,
                    anchor:["Top"],
                    hoverPaintStyle: endpointHoverStyle,
                    connectorHoverStyle: connectorHoverStyle,
                    dragOptions: {},
                    maxConnections: -1
                },
                // the definition of target endpoints (will appear when the user drags a connection)
                //anchor:["Perimeter",{"shape":"Circle"}],
                targetEndpoint = {
                    endpoint: "Dot",
                    paintStyle: { fill: "#7AB02C", radius: 1 },
                    hoverPaintStyle: endpointHoverStyle,
                    anchor:["Top"],
                    maxConnections: -1,
                    dropOptions: { hoverClass: "hover", activeClass: "active" },
                    isTarget: true
                },
                init = function (connection) {
                    var _k = parseInt(connection.targetId.substring(3));
                    //console.log(' init targetId ' + connection.targetId + '  ' + _k + '     ' + JSON.stringify(_this.words[_k]) );
                    //connection.getOverlay("label").setLabel( {label: _this.words[_k].relate, location: 0.9 } );
                    //console.log( (_loc[_k] / _dis[_k]).toFixed(1) + "  location ");
                    connection.setLabel( {label: _this.words[_k].relate, location: 0.8, labelStyle:{cssClass:"dp-label"} } );
                };

                var _addEndpoints = function (sourceId, toId) {

                		var intK = parseInt(toId);
                        var sourceUUID = "s" + sourceId;

                        instance.addEndpoint("dp-" + sourceId, sourceEndpoint, {
                            anchor: _this.sourceAnchors, uuid: sourceUUID, connector: [ "Flowchart", { stub: 30 + 10 *  dpConnections[intK].stub, gap: 1, cornerRadius: 3, alwaysRespectStubs: true } ],
                        });
                        //console.log("sourceUUID "  + sourceUUID+ " stub: " + (30 + 10 * Math.abs(parseInt(sourceId) - parseInt(toId))) );

                        var targetUUID = "t" + toId;
                        instance.addEndpoint("dp-" + toId, targetEndpoint, { anchor: _this.targetAnchors, uuid: targetUUID });
                        //console.log("targetUUID "  + targetUUID);

                };

                // suspend drawing and initialise.
                instance.batch(function () {

                    for (var _k in _this.words)
                    {
                        _addEndpoints(String(_this.words[_k].head), String(_k));
                    }

                    // listen for new connections; initialise them the same way we initialise the connections at startup.
                    instance.bind("connection", function (connInfo, originalEvent) {
                        init(connInfo.connection);
                    });

                    // connect a few up
                    //instance.connect({uuids: ["Window2BottomCenter", "Window3TopCenter"], editable: true});
                    for (var _k in _this.words)
                    {
                        instance.connect({uuids: [ "s" + _this.words[_k].head, "t" + _k]});
                    }

                    // listen for clicks on connections, and offer to delete connections on click.
                    instance.bind("click", function (conn, originalEvent) {
                       //conn.toggleType("basic");
                    });

                    /*
                    instance.bind("connectionDrag", function (connection) {
                        console.log("connection " + connection.id + " is being dragged. suspendedElement is ", connection.suspendedElement, " of type ", connection.suspendedElementType);
                    });
                    */
                });
			};
			// drawFlowChart END

		}
		else
		{
			return new _dp(_options);
		}
	}

	_dp.prototype = {
		constructor:_dp
	}

	return _dp;
})();
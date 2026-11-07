/* AGCM offline QR encoder (byte mode, ECC level M, versions 1-16).
 * This produces SVG entirely in the administrator's browser, without sharing the invitation URL.
 * QR Model 2 encoding: ISO/IEC 18004. */
(function (root) {
  'use strict';
  // Generated from the public QR Code Model 2 RS block specification (ECC M).
  var TABLE = {"blocks":{"1":[[26,16]],"2":[[44,28]],"3":[[70,44]],"4":[[50,32],[50,32]],"5":[[67,43],[67,43]],"6":[[43,27],[43,27],[43,27],[43,27]],"7":[[49,31],[49,31],[49,31],[49,31]],"8":[[60,38],[60,38],[61,39],[61,39]],"9":[[58,36],[58,36],[58,36],[59,37],[59,37]],"10":[[69,43],[69,43],[69,43],[69,43],[70,44]],"11":[[80,50],[81,51],[81,51],[81,51],[81,51]],"12":[[58,36],[58,36],[58,36],[58,36],[58,36],[58,36],[59,37],[59,37]],"13":[[59,37],[59,37],[59,37],[59,37],[59,37],[59,37],[59,37],[59,37],[60,38]],"14":[[64,40],[64,40],[64,40],[64,40],[65,41],[65,41],[65,41],[65,41],[65,41]],"15":[[65,41],[65,41],[65,41],[65,41],[65,41],[66,42],[66,42],[66,42],[66,42],[66,42]],"16":[[73,45],[73,45],[73,45],[73,45],[73,45],[73,45],[73,45],[74,46],[74,46],[74,46]],"17":[[74,46],[74,46],[74,46],[74,46],[74,46],[74,46],[74,46],[74,46],[74,46],[74,46],[75,47]]},"pos":{"1":[],"2":[6,18],"3":[6,22],"4":[6,26],"5":[6,30],"6":[6,34],"7":[6,22,38],"8":[6,24,42],"9":[6,26,46],"10":[6,28,50],"11":[6,30,54],"12":[6,32,58],"13":[6,34,62],"14":[6,26,46,66],"15":[6,26,48,70],"16":[6,26,50,74],"17":[6,30,54,78]}};
  function gfTables() {
    var exp = new Array(512), log = new Array(256).fill(0), x = 1;
    for (var i=0;i<255;i++) { exp[i]=x; log[x]=i; x<<=1; if(x&256) x^=0x11d; }
    for (var j=255;j<512;j++) exp[j]=exp[j-255];
    return {exp:exp, log:log};
  }
  var gf = gfTables();
  function mul(a,b) { return a && b ? gf.exp[gf.log[a]+gf.log[b]] : 0; }
  function divisor(length) {
    var poly=[1];
    for (var i=0;i<length;i++) {
      var tmp=new Array(poly.length+1).fill(0);
      for(var j=0;j<poly.length;j++) { tmp[j]^=poly[j];tmp[j+1]^=mul(poly[j],gf.exp[i]); }
      poly=tmp;
    }
    return poly.slice(1);
  }
  function remainder(data, len) {
    var div=divisor(len), rem=new Array(len).fill(0);
    data.forEach(function(value){
      var factor=value^rem[0];
      rem.shift(); rem.push(0);
      for(var j=0;j<len;j++) rem[j]^=mul(div[j],factor);
    });
    return rem;
  }
  function bitlen(n) { var count=0;while(n){count++;n>>>=1;}return count; }
  function bch(value,poly,shift) {
    var rem=value<<shift;
    while(bitlen(rem)>=bitlen(poly)) rem^=poly<<(bitlen(rem)-bitlen(poly));
    return (value<<shift)|rem;
  }
  function mask(row,col) { return ((row+col)&1)===0; } // Mask pattern 0.
  function make(text) {
    var bytes = (new TextEncoder()).encode(text);
    var version, blocks, capacity;
    for(version=1;version<=16;version++) {
      blocks=TABLE.blocks[String(version)];
      capacity=blocks.reduce(function(sum,b){return sum+b[1];},0);
      var bits=4+(version<10?8:16)+bytes.length*8;
      if (bits<=capacity*8) break;
    }
    if(version>16) throw new Error('QRコード用URLが長すぎます');
    var dataBits=[];
    function append(value,length) { for(var i=length-1;i>=0;i--) dataBits.push((value>>>i)&1); }
    append(4,4);append(bytes.length,version<10?8:16);
    bytes.forEach(function(v){append(v,8);});
    append(0,Math.min(4,capacity*8-dataBits.length));
    while(dataBits.length%8) dataBits.push(0);
    var data=[];
    for(var b=0;b<dataBits.length;b+=8) {
      var val=0;for(var k=0;k<8;k++)val=(val<<1)|dataBits[b+k];data.push(val);
    }
    for(var q=0;data.length<capacity;q++)data.push(q%2===0?0xec:0x11);
    var dataBlocks=[],eccBlocks=[],ptr=0;
    blocks.forEach(function(info){
      var segment=data.slice(ptr,ptr+info[1]); ptr+=info[1];
      dataBlocks.push(segment);eccBlocks.push(remainder(segment,info[0]-info[1]));
    });
    var joined=[];
    var maxData=Math.max.apply(null,dataBlocks.map(function(x){return x.length;}));
    var maxEcc=Math.max.apply(null,eccBlocks.map(function(x){return x.length;}));
    for(var col=0;col<maxData;col++)dataBlocks.forEach(function(x){if(col<x.length) joined.push(x[col]);});
    for(var col2=0;col2<maxEcc;col2++)eccBlocks.forEach(function(x){if(col2<x.length) joined.push(x[col2]);});
    var n=17+4*version,m=new Array(n);
    for(var r=0;r<n;r++)m[r]=new Array(n).fill(null);
    function put(row,col,dark) { if(row>=0&&row<n&&col>=0&&col<n)m[row][col]=!!dark; }
    function finder(row,col) {
      for(var dr=-1;dr<=7;dr++)for(var dc=-1;dc<=7;dc++) {
        var dark=(dr>=0&&dr<=6&&(dc===0||dc===6))||(dc>=0&&dc<=6&&(dr===0||dr===6))||(dr>=2&&dr<=4&&dc>=2&&dc<=4);
        put(row+dr,col+dc,dark);
      }
    }
    finder(0,0);finder(n-7,0);finder(0,n-7);
    var pos=TABLE.pos[String(version)];
    pos.forEach(function(row){pos.forEach(function(col){
      if(m[row][col]!==null)return;
      for(var dr=-2;dr<=2;dr++)for(var dc=-2;dc<=2;dc++)put(row+dr,col+dc, Math.abs(dr)===2||Math.abs(dc)===2||(dr===0&&dc===0));
    });});
    for(var t=8;t<n-8;t++){if(m[t][6]===null)put(t,6,t%2===0);if(m[6][t]===null)put(6,t,t%2===0);}
    var format=bch(0,0x537,10)^0x5412;
    for(var i=0;i<15;i++) {
      var dark=((format>>>i)&1)===1;
      put(i<6?i:i<8?i+1:n-15+i,8,dark);
      put(8,i<8?n-i-1:i<9?15-i:14-i,dark);
    }
    put(n-8,8,true);
    if(version>=7){
      var ver=bch(version,0x1f25,12);
      for(var i=0;i<18;i++){
        var dark=((ver>>>i)&1)===1;
        put(Math.floor(i/3),i%3+n-11,dark);
        put(i%3+n-11,Math.floor(i/3),dark);
      }
    }
    var bit=7,byte=0,direction=-1,row=n-1;
    for(var x=n-1;x>0;x-=2){
      if(x<=6)x--;
      while(true){
        for(var dx=0;dx<2;dx++){
          var c=x-dx;
          if(m[row][c]!==null)continue;
          var active=byte<joined.length ? (((joined[byte]>>>bit)&1)===1) : false;
          put(row,c,active!==mask(row,c));
          if(--bit<0){byte++;bit=7;}
        }
        row+=direction;
        if(row<0||row>=n){row-=direction;direction=-direction;break;}
      }
    }
    var d='',quiet=4;
    for(var y=0;y<n;y++)for(var xx=0;xx<n;xx++)if(m[y][xx])d+='M'+(xx+quiet)+' '+(y+quiet)+'h1v1h-1z';
    var size=n+8;
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '+size+' '+size+'" shape-rendering="crispEdges"><rect width="'+size+'" height="'+size+'" fill="#fff"/><path d="'+d+'" fill="#000"/></svg>';
  }
  root.agcmQrSvg = make;
})(typeof globalThis === 'object' ? globalThis : this);

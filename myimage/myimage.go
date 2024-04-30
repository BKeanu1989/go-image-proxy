package myimage

import (
	"fmt"
	"net/url"
	"path"
	"strconv"
	"strings"
	// "net/url"
)

type MyImage struct{}

type MyOptions struct {
	// See ParseOptions for interpretation of Width and Height values
	Width  float64
	Height float64

	// Quality of output image
	Quality int

	// HMAC Signature for signed requests.
	Signature string

	OriginalUrl string

	// Desired image format. Valid values are "jpeg", "png".
	Format string
}

func (opts MyOptions) GetFileName() string {

	base := path.Base(opts.OriginalUrl)
	s := strings.Split(base, ".")

	// ext := path.Ext(opts.OriginalUrl)

	wi := strconv.FormatFloat(opts.Width, 'f', 0, 64)
	hi := strconv.FormatFloat(opts.Height, 'f', 0, 64)
	qi := strconv.FormatInt(int64(opts.Quality), 10)
	s[0] += "--" + "w-" + wi + "-h-" + hi + "-q-" + qi
	s[1] = opts.Format
	fmt.Printf("%v", s)
	return strings.Join(s, ".")

}

func UrlParser(path string) MyOptions {
	var w, h float64
	var q int
	var urlVal string
	var f string

	myUrl, _ := url.Parse(path)
	params, _ := url.ParseQuery(myUrl.RawQuery)

	if val, ok := params["h"]; ok {
		hv, err := strconv.Atoi(val[0])
		if err == nil {
			h = float64(hv)
		}
	}

	if val, ok := params["w"]; ok {
		wv, err := strconv.Atoi(val[0])
		if err == nil {
			w = float64(wv)
		}
	}

	if val, ok := params["q"]; ok {
		qv, err := strconv.Atoi(val[0])
		// fmt.Printf("quality is %d", qv)
		if err == nil {
			q = qv
		}

	} else {

		q = 80
	}

	if val, ok := params["url"]; ok {
		urlVal = val[0]
	}

	val, ok := params["f"]
	if ok {
		switch val[0] {
		case "jpeg", "jpg":
			f = "jpeg"
		case "png":
			f = "png"
		default:
			f = "jpeg"
		}
	} else {
		f = "jpeg"
	}

	opt := MyOptions{
		Width:       w,
		Height:      h,
		Quality:     q,
		OriginalUrl: urlVal,
		Format:      f,
	}

	return opt

}

// http://localhost:8080/render/?url=https://kunststoffplattenprofis.de/wp-content/uploads/2021/10/Titel-Test1.png&f=jpeg&s=100
func UrlParserForRendering(p string) MyRenderOptios {
	var s int

	myUrl, _ := url.Parse(p)
	params, _ := url.ParseQuery(myUrl.RawQuery)
	if val, ok := params["s"]; ok {
		sv, err := strconv.Atoi(val[0])
		// fmt.Printf("quality is %d", qv)
		if err == nil {
			s = sv
		} else {
			s = 0
		}
	}

	opt := MyRenderOptios{

		// MyOptions: ,
		// Width: 400,
		// MyOptions: ,
	}

}

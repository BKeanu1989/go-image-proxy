package myimage

import (
	"fmt"
	"net/url"
	"path"
	"strconv"
	// "net/url"
)

type MyImage struct{}

type MyOptions struct {
	// See ParseOptions for interpretation of Width and Height values
	Width  float64
	Height float64

	// If true, resize the image to fit in the specified dimensions.  Image
	// will not be cropped, and aspect ratio will be maintained.
	Fit bool

	// Rotate image the specified degrees counter-clockwise.  Valid values
	// are 90, 180, 270.
	Rotate int

	FlipVertical   bool
	FlipHorizontal bool

	// Quality of output image
	Quality int

	// HMAC Signature for signed requests.
	Signature string

	OriginalUrl string

	// Desired image format. Valid values are "jpeg", "png", "tiff".
	Format string
}

func (opts MyOptions) getFileName() string {
	base := path.Base(opts.OriginalUrl)
	ext := path.Ext(opts.OriginalUrl)
	clean := path.Clean(opts.OriginalUrl)

	fmt.Printf("%s - ext: %s, clean: %s", base, ext, clean)

	return ""

}

func UrlParser(path string) MyOptions {
	var w, h float64
	var q int
	var urlVal string

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
		if err == nil {
			q = qv
		}
	}

	if val, ok := params["url"]; ok {
		urlVal = val[0]
	}

	opt := MyOptions{
		Width:       w,
		Height:      h,
		Quality:     q,
		OriginalUrl: urlVal,
	}

	return opt

}

package request

import (
	"net/http"
	"net/url"

	"example.com/image-proxy/myimage"
)

type Request struct {
	URL      *url.URL          // URL of the image to proxy
	Options  myimage.MyOptions // Image transformation to perform
	Original *http.Request     // The original HTTP request
}

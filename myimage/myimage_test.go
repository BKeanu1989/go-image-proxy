package myimage

import (
	"testing"
)

func TestUrlParser(t *testing.T) {
	foo := "https://test.foo/image/?url=http://localhost:3000&w=500&h=400&q=80"

	opts := UrlParser(foo)

	expectedHeight := 400.0
	if opts.Height != expectedHeight {
		t.Fatalf("Height should be %f, but is %f", expectedHeight, opts.Height)
	}

	expectedWidth := 500.0
	if opts.Width != expectedWidth {
		t.Fatalf("Width should be %f, but is %f", expectedWidth, opts.Width)
	}

	expectedQuality := 80
	if opts.Quality != expectedQuality {
		t.Fatalf("Width should be %d, but is %d", expectedQuality, opts.Quality)
	}

	expectedUrl := "http://localhost:3000"
	if opts.OriginalUrl != expectedUrl {
		t.Fatalf("Original Url should be %s, but is %s", expectedUrl, opts.OriginalUrl)
	}
}

func TestGetFileName(t *testing.T) {

	foo := MyOptions{Width: 400,
		Height:      500,
		Quality:     90,
		OriginalUrl: "https://kunststoffplattenprofis.de/wp-content/uploads/2021/02/Titel-Test1.png",
	}

	expectedVal := foo.GetFileName()
	if expectedVal != "Titel-Test1--w-400-h-500-q-90.png" {
		t.Fatalf("Is not the supposed file name: %s", expectedVal)
	}
}

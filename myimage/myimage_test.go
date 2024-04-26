package myimage

import (
	"testing"
)

func TestUrlParser(t *testing.T) {
	foo := "https://test.foo/image/?url=http://localhost:3000&w=500&h=400&q=80&f=png"

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
		t.Fatalf("Quality should be %d, but is %d", expectedQuality, opts.Quality)
	}

	expectedUrl := "http://localhost:3000"
	if opts.OriginalUrl != expectedUrl {
		t.Fatalf("Original Url should be %s, but is %s", expectedUrl, opts.OriginalUrl)
	}

	expectedFormat := "png"
	if opts.Format != expectedFormat {
		t.Fatalf("Expected format should be %s but is %s", expectedFormat, opts.Format)
	}
}

func TestUrlParserDefaults(t *testing.T) {
	foo := "http://localhost:8090/image/?url=http://localhost:8080/wp-content/uploads/2022/07/Tobias-Kasimirowicz_%C2%A9Jacqueline-Schulz-9.jpg&w=215"

	opts := UrlParser(foo)

	expectedWidth := 215.0
	if opts.Width != expectedWidth {
		t.Fatalf("Width should be %f, but is %f", expectedWidth, opts.Width)
	}

	expectedQuality := 80
	if opts.Quality != expectedQuality {
		t.Fatalf("Quality should be %d, but is %d", expectedQuality, opts.Quality)
	}

	expectedUrl := "http://localhost:8080/wp-content/uploads/2022/07/Tobias-Kasimirowicz_©Jacqueline-Schulz-9.jpg"
	if opts.OriginalUrl != expectedUrl {
		t.Fatalf("Original Url should be %s, but is %s", expectedUrl, opts.OriginalUrl)
	}

	expectedFormat := "jpeg"
	if opts.Format != expectedFormat {
		t.Fatalf("Expected format should be %s but is %s", expectedFormat, opts.Format)
	}
}

//

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

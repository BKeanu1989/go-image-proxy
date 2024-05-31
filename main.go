package main

import (
	"bytes"
	"encoding/base64"
	"encoding/json"
	"fmt"
	"image"
	"image/gif"
	"image/jpeg"
	"image/png"
	"io"
	"log"
	"net/http"
	"os"
	"path"
	"strings"
	"time"

	"example.com/image-proxy/myimage"
	"example.com/image-proxy/view"
	"example.com/image-proxy/view/layout"
	"example.com/image-proxy/view/partial"
	"github.com/a-h/templ"

	// "github.com/h2non/bimg"

	// "github.com/h2non/bimg"
	"github.com/patrickmn/go-cache"
	"github.com/sirupsen/logrus"
	"github.com/sunshineplan/imgconv"
)

// TODO: key & salt for signing

// sha?
// cache - fs &/ memory => hash map[hash of filename] = base64 || with max size for fs & memory

// "http://localhost:8090/images?url=http://localhost:3333/&w=500&h=500&q=80"

// http://localhost:8090/images?url=http://kunststoffplattenprofis.de/&w=500&h=500&q=80

// http://localhost:4444/image/?url=https://kunststoffplattenprofis.de/wp-content/uploads/2021/10/Titel-Test1.png&w=500&h=500&q=4
// http://localhost:4444/render/?url=https://kunststoffplattenprofis.de/wp-content/uploads/2021/10/Titel-Test1.png&f=jpeg&s=100
// http://localhost:8090/image/?url=http://localhost:8080/wp-content/uploads/2022/07/Tobias-Kasimirowicz_%C2%A9Jacqueline-Schulz-9.jpg&w=215q=80&f=jpeg
// var log = logrus.New()

func main() {
	f, err := os.OpenFile("./logs/debug.log", os.O_WRONLY|os.O_CREATE|os.O_APPEND, 0755)

	if err != nil {
		fmt.Printf("logger cant open file")
	}
	logrus.SetOutput(f)

	logrus.Info("test write to logger")
	mux := http.NewServeMux()

	c := cache.New(30*time.Minute, 50*time.Minute)

	cmp := layout.Base(view.Index())
	mux.Handle("/", templ.Handler(cmp))

	mux.Handle("/foo", templ.Handler(partial.Foo()))
	mux.HandleFunc("/render", func(w http.ResponseWriter, r *http.Request) {
		opt := myimage.UrlParser(r.URL.RequestURI())
		partial.Responsive_Image(opt).Render(r.Context(), w)
	})
	// mux.HandleFunc("GET /", templ.Handler(cmp))

	// mux.HandleFunc("GET /", func(w http.ResponseWriter, r *http.Request) {
	// 	fmt.Fprintf(w, "Hello, World!")

	// })

	// http.Handle("GET /", templ.Handler(comp))

	mux.HandleFunc("/image", func(w http.ResponseWriter, r *http.Request) {
		// mux.HandleFunc("GET /image/", func(w http.ResponseWriter, r *http.Request) {
		defer timer("image conversion")()
		fmt.Println("Hit endpoint /image/")
		fmt.Printf("%+v", r.UserAgent())
		currentTime := time.Now()
		fmt.Printf("%d-%d-%d %d:%d:%d\n",
			currentTime.Year(),
			currentTime.Month(),
			currentTime.Day(),
			currentTime.Hour(),
			currentTime.Hour(),
			currentTime.Second())

		opts := myimage.UrlParser(r.URL.RequestURI())

		imageInCache, ok := c.Get(opts.GetFileName())

		if ok {
			fmt.Println("serve from cache")
			w.Header().Set("Content-Disposition", "inline")
			w.Header().Set("Content-Type", "image/"+opts.Format)

			w.Write(imageInCache.([]byte))

			return
		}

		img, format, err := foundImageInFs(opts)
		if err != nil {
			fmt.Println("Handle new image")
			dwlImage, err := downloadImage(opts.OriginalUrl)
			logrus.Info("downloading image from: ")
			logrus.Info(opts.OriginalUrl)

			if err != nil {
				log.Fatal(err)
				logrus.Warn("cannot download image")
				logrus.Warn(err)
			}

			src, err := resizeNQualityImage(dwlImage, opts)
			if err != nil {
				log.Fatal(err)
				logrus.Warn("cannot resize/convert image")
				logrus.Warn(err)
			}

			buf, err := os.ReadFile(src)
			if err != nil {
				log.Fatal(err)
				logrus.Warn("cannot read file")
				logrus.Warn(err)
			}

			c.Set(opts.GetFileName(), buf, cache.DefaultExpiration)
			// TODO: set header according to image format
			w.Header().Set("Content-Disposition", "inline")
			w.Header().Set("Content-Type", "image/"+opts.Format)
			w.Write(buf)

		} else {
			fmt.Println("Handle old image")

			buf := new(bytes.Buffer)
			switch format {
			case "jpeg", "jpg":
				jpeg.Encode(buf, img, nil)
			case "png":
				png.Encode(buf, img)
				// case "gif":
				// 	gif.Encode(buf, img, nil)
			default:
				fmt.Errorf("unsupported format: %s", format)
			}
			c.Set(opts.GetFileName(), buf.Bytes(), cache.DefaultExpiration)
			// foundImageInFs should just return buffer
			w.Header().Set("Content-Disposition", "inline")
			w.Header().Set("Content-Type", "image/"+format)
			w.Write(buf.Bytes())

		}

	})

	mux.HandleFunc("/health", func(w http.ResponseWriter, r *http.Request) {
		// mux.HandleFunc("GET /health", func(w http.ResponseWriter, r *http.Request) {
		p := struct{}{}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(p)
	})

	port := "8080"
	fmt.Printf("Starting server on port %v\n", port)
	// err := http.ListenAndServe("localhost:"+port, mux)
	err = http.ListenAndServe("0.0.0.0:"+port, mux)
	if err != nil {
		fmt.Fprintf(os.Stderr, "Can't listen on port %q: %s", port, err)
		logrus.Warn(fmt.Printf("cant listen on port %s", port))
		os.Exit(1)
	}
}

func downloadImage(p string) (string, error) {
	client := http.Client{
		Timeout: 5 * time.Second,
	}
	ext := path.Ext(p)
	tmpFileName := "./base/" + strings.Replace(base64Encode(p), "/", "", -1) + "." + ext
	if ext == "" {
		log.Fatal("No Extension seen in request url")
		logrus.Warn("no extension provided in request url")
	}
	// error fetching image in app
	response, err := client.Get(p)
	if err != nil {
		fmt.Println("Error fetching image:", err)
		logrus.Warn("error fetching image")
		logrus.Warn(err)
		return "", err
	}
	defer response.Body.Close()

	file, err := os.Create(tmpFileName)
	if err != nil {
		logrus.Warn("error creating file")
		logrus.Warn(err)
		return "", err
	}
	defer file.Close()

	_, err = io.Copy(file, response.Body)
	if err != nil {
		logrus.Warn("errir saving image")
		logrus.Warn(err)
		return "", err
	}

	return tmpFileName, nil
}

func resizeNQualityImage(srcPath string, opt myimage.MyOptions) (string, error) {

	logrus.Info("my image options are:")
	logrus.Info(opt)

	src, err := imgconv.Open(srcPath)
	var mark image.Image
	if err != nil {
		log.Fatalf("failed to open image: %v", err)
		logrus.Warn("failed top open image")
		logrus.Warn(err)
	}

	if opt.Quality != 0 {
		imgconv.Quality(opt.Quality)
	}

	// if opt.Height != 0.0 {
	// 	if opt.Width != 0.0 {
	// 		mark = imgconv.Resize(src, &imgconv.ResizeOption{Height: int(opt.Height), Width: int(opt.Width)})
	// 	} else {
	// 		mark = imgconv.Resize(src, &imgconv.ResizeOption{Width: int(opt.Width)})
	// 	}
	// } else {
	// 	mark = imgconv.Resize(src, &imgconv.ResizeOption{Width: int(opt.Width)})
	// }

	if opt.Height != 0.0 && opt.Width != 0.0 {
		mark = imgconv.Resize(src, &imgconv.ResizeOption{Height: int(opt.Height), Width: int(opt.Width)})
	} else if opt.Height != 0.0 || opt.Width != 0.0 {
		dim := imgconv.ResizeOption{}
		if opt.Width != 0.0 {
			dim.Width = int(opt.Width)
		}
		if opt.Height != 0.0 {
			dim.Height = int(opt.Height)
		}
		mark = imgconv.Resize(src, &dim)
	} else {
		// is this correct?
		mark = src
	}

	// resizedImage := imaging.Resize(src, 300, 200, imaging.Lanczos)

	// Save the resized image to a file
	outFile := "./out/" + opt.GetFileName()

	err = encodeImage(outFile, mark, opt.Format, opt.Quality)

	// TODO: support more image formats
	// err = imgconv.Save(outFile, mark, &imgconv.FormatOption{Format: imgconv.JPEG})
	if err != nil {
		logrus.Warn("cannot encode image")
		logrus.Warn(err)
		panic(err)
	}

	return outFile, nil

}

// func resizeNQualityImage_refactored(srcPath string, opt myimage.MyOptions) (string, error) {
// 	buffer, err := bimg.Read(srcPath)
// 	if err != nil {
// 		fmt.Fprintln(os.Stderr, err)
// 		return "", err
// 	}

// 	newImage, err := bimg.NewImage(buffer).Convert(bimg.WEBP)
// 	if err != nil {
// 		fmt.Fprintln(os.Stderr, err)
// 		return "", err
// 	}

// 	outFile := "./out/" + opt.GetFileName()
// 	bimg.Write(outFile, newImage)

// 	return outFile, nil
// }

func base64Encode(str string) string {
	return base64.StdEncoding.EncodeToString([]byte(str))
}

func base64Decode(str string) (string, bool) {
	data, err := base64.StdEncoding.DecodeString(str)
	if err != nil {
		return "", true
	}
	return string(data), false
}

func encodeImage(filename string, img image.Image, format string, quality int) error {
	file, err := os.Create(filename)
	if err != nil {
		return err
	}
	defer file.Close()

	switch format {
	case "jpeg", "jpg":
		fmt.Printf("chosen file format is jpeg and should apply quality of %d", quality)
		return jpeg.Encode(file, img, &jpeg.Options{Quality: quality})
	case "png":
		return png.Encode(file, img)
	case "gif":
		return gif.Encode(file, img, nil)
	default:
		return fmt.Errorf("unsupported format: %s", format)
	}
}

func timer(name string) func() {
	start := time.Now()
	return func() {
		fmt.Printf("%s took %v\n", name, time.Since(start))
	}
}

func foundImageInFs(opts myimage.MyOptions) (image.Image, string, error) {
	// f, err :=
	f, err := os.Open("./out/" + opts.GetFileName())
	// defer f.Close()
	if err != nil {
		return nil, "", err
	}
	image, format, err := image.Decode(f)
	return image, format, err
}

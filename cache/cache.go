package cache

// import (
// 	"fmt"
// 	"github.com/patrickmn/go-cache"
// 	"time"
// )

// type Cache struct {
// }

type Cache interface {
	// Get retrieves the cached data for the provided key.
	Get(key string) (data []byte, ok bool)

	// Set caches the provided data.
	Set(key string, data []byte)

	// Delete deletes the cached data at the specified key.
	Delete(key string)
}

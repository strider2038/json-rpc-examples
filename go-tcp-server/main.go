// Program server demonstrates how to set up a JSON-RPC 2.0 server using the
// bitbucket.org/creachadair/jrpc2 package.
//
// Usage (see also the client example):
//
//   go build bitbucket.org/creachadair/jrpc2/examples/server
//   ./server -port 8080
//
package main

import (
	"bitbucket.org/creachadair/jrpc2/channel"
	"context"
	"flag"
	"fmt"
	"log"
	"net"
	"os"

	"bitbucket.org/creachadair/jrpc2"
	"bitbucket.org/creachadair/jrpc2/metrics"
	"bitbucket.org/creachadair/jrpc2/server"
)

// Add returns the sum of vs, or 0 if len(vs) == 0.
func sum(ctx context.Context, vs []int) (int, error) {
	sum := 0

	for _, v := range vs {
		sum += v
	}

	return sum, nil
}

// Status simulates a health check, reporting "OK" to all callers.  It also
// demonstrates the use of server-side push.
func status(ctx context.Context) (string, error) {
	if err := jrpc2.ServerPush(ctx, "pushback", []string{"hello, friend"}); err != nil {
		return "BAD", err
	}

	return "OK", nil
}

type alertMessage struct {
	Message string `json:"message"`
}

// alert implements a notification handler that logs its argument.
func alert(ctx context.Context, a alertMessage) error {
	log.Printf("[ALERT]: %s", a.Message)

	return nil
}

var (
	port     = flag.Int("port", 0, "Service port")
	maxTasks = flag.Int("max", 1, "Maximum concurrent tasks")
)

func main() {
	flag.Parse()

	if *port <= 0 {
		log.Fatal("You must provide a positive -port to listen on")
	}

	mux := jrpc2.ServiceMapper{
		"math": jrpc2.MapAssigner{
			"sum": jrpc2.NewHandler(sum),
		},
		"action": jrpc2.MapAssigner{
			"alert":  jrpc2.NewHandler(alert),
			"status": jrpc2.NewHandler(status),
		},
	}

	lst, err := net.Listen("tcp", fmt.Sprintf("localhost:%d", *port))
	if err != nil {
		log.Fatalln("Listen:", err)
	}

	log.Printf("Listening at %v...", lst.Addr())

	err = server.Loop(lst, mux, &server.LoopOptions{
		Framing: channel.Line,
		ServerOptions: &jrpc2.ServerOptions{
			Logger:      log.New(os.Stderr, "[jrpc2.Server] ", log.LstdFlags|log.Lshortfile),
			Concurrency: *maxTasks,
			Metrics:     metrics.New(),
			AllowPush:   false,
		},
	})

	log.Fatal(err)
}

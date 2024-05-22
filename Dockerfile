# syntax=docker/dockerfile:1

FROM golang:latest as build

# Set destination for COPY
WORKDIR /app

# Download Go modules
COPY go.mod go.sum ./
RUN go mod download

# Copy the source code. Note the slash at the end, as explained in
# https://docs.docker.com/engine/reference/builder/#copy
# COPY *.go ./
COPY main.go ./
COPY myimage ./myimage/
COPY view ./view

# Build
# RUN go build -o go-image-proxy
# RUN GOOS=linux go build -o go-image-proxy
RUN CGO_ENABLED=0 GOOS=linux go build -o go-image-proxy


# Use a Docker multi-stage build to create a lean production image.

FROM alpine:latest

WORKDIR /app

# Copy the binary to the production image from the builder stage.
COPY --from=build /app/go-image-proxy .
RUN mkdir base
RUN mkdir out

RUN apk update
RUN apk add curl




# Optional:
# To bind to a TCP port, runtime parameters must be supplied to the docker command.
# But we can document in the Dockerfile what ports
# the application is going to listen on by default.
# https://docs.docker.com/engine/reference/builder/#expose
EXPOSE 8080

# Run
# RUN ls -la
# CMD ["ls", "-la"]
# gives
# 2024-05-13 15:27:13 total 10704
# 2024-05-13 15:27:13 drwxr-xr-x    1 root     root          4096 May 13 13:02 .
# 2024-05-13 15:27:13 drwxr-xr-x    1 root     root          4096 May 13 13:27 ..
# 2024-05-13 15:27:13 -rwxr-xr-x    1 root     root      10952567 May 13 13:02 go-image-proxy
CMD ["./go-image-proxy"]
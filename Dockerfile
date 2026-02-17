# Use the lightweight Nginx Alpine image
FROM nginx:alpine

# Remove default nginx static assets
RUN rm -rf /usr/share/nginx/html/*

# Copy your website files into the Nginx server directory
# This assumes your index.html is in a folder named 'website'
COPY ./website /usr/share/nginx/html

# Expose port 80
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
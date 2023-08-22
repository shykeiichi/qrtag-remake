# QRTag Remake

En remake av [QRTag](https://git.ssis.nu/togethernet/qrtag) som gjordes av Movitz Sunar och Skye Kaijser

# Openshift Installation

Skapa ett projekt i [skolans openshift](https://console-openshift-console.apps.okd.ssis.nu) och gå in på "Administrator" till vänster i sidebaren.  

## QRTag

### Hemsida

Den här deployment filen lägger du under `Workloads`>`Deployments`>`Create Deployment`>`YAML view` och kopierar in den där och sedan klickar du på `Create`.
```yaml
kind: Deployment
apiVersion: apps/v1
metadata:
  name: qrtag
  namespace: qrtag
  labels:
    app: qrtag
spec:
  replicas: 1
  selector:
    matchLabels:
      app: qrtag
  template:
    metadata:
      labels:
        app: qrtag
    spec:
      initContainers:
        - resources: {}
          name: migratedb
          command:
            - php
          imagePullPolicy: Always
          terminationMessagePolicy: File
          envFrom:
            - configMapRef:
                name: qrtag-env
            - secretRef:
                name: qrtag-secrets
          image: 'gitregistry.ssis.nu/togethernet/qrtag-remake:latest'
          args:
            - artisan
            - migrate
            - '--force'
      containers:
        - name: qrtag
          image: 'gitregistry.ssis.nu/togethernet/qrtag-remake:latest'
          ports:
            - name: http
              containerPort: 8080
              protocol: TCP
          envFrom:
            - secretRef:
                name: qrtag-secrets
            - configMapRef:
                name: qrtag-env
          resources:
            limits:
              cpu: '2'
              memory: 1Gi
            requests:
              cpu: 250m
              memory: 128Mi
```

Den här config mappen lägger du under `Workloads`>`ConfigMaps`>`Create ConfigMap`>`YAML view` och kopierar in den där och sedan klickar du på `Create`.
```yaml
kind: ConfigMap
apiVersion: v1
metadata:
  name: qrtag-env
  namespace: qrtag
data:
  DB_PORT: '3306'
  TRUSTED_PROXIES: 10.0.0.0/8
  DB_HOST: qrtag-database
  APP_URL: 'https://qrtag.ssis.nu'
  MAINTAINER_NAME: <Namn av maintainern>
  LDAPTLS_REQCERT: never
  LDAP_PORT: '636'
  LDAP_USE_SSL: 'true'
  DB_CONNECTION: mysql
  DB_DATABASE: qrtag
```

Sedan lägger du in dina secrets i `Workloads`>`Secrets`>`Create`>`From YAML`
```yaml
apiVersion: v1
kind: Secret
metadata:
  name: qrtag-secrets
  namespace: qrtag
type: Opaque
stringData:
  APP_KEY: <Din laravel app key som du kan skaffa online på https://laravel-encryption-key-generator.vercel.app/>
  DB_PASSWORD: <Lösenordet för din databas>
  DB_USERNAME: <Användarnamnet för din databas>
  DISCORD_WEBHOOK: <En discord webhook där qrtag event loggen ska skickas >
```

Skapa en service under `Networking`>`Services`>`Create Service`
```yaml
kind: Service
apiVersion: v1
metadata:
  name: qrtag
  namespace: qrtag
spec:
  ports:
    - name: http
      protocol: TCP
      port: 8080
      targetPort: 8080
  clusterIPs:
    - 172.30.85.69
  selector:
    app: qrtag
```

Skapa en route under `Networking`>`Routes`>`Create`>`YAML view`
```yaml
kind: Route
apiVersion: route.openshift.io/v1
metadata:
  name: qrtag
  namespace: qrtag
spec:
  path: /
  to:
    kind: Service
    name: qrtag
    weight: 100
  port:
    targetPort: http
  tls:
    termination: edge
    insecureEdgeTerminationPolicy: Redirect
```

## Databas

Skapa en databas deployment under `Workloads`>`Deployments`>`Create Deployment`>`YAML view`
```yaml
kind: Deployment
apiVersion: apps/v1
metadata:
  name: database
  namespace: qrtag
  labels:
    app: qrtag-database
spec:
  replicas: 1
  revisionHistoryLimit: 3
  selector:
    matchLabels:
      app: qrtag-database
  template:
    metadata:
      labels:
        app: qrtag-database
    spec:
      restartPolicy: Always
      containers:
        - name: database
          image: mariadb:latest
          imagePullPolicy: IfNotPresent
          envFrom:
            - secretRef:
                name: database-secret
          resources:
            limits:
              cpu: '4'
              memory: 4Gi
            requests:
              cpu: '1'
              memory: 2Gi
          ports:
            - name: mysql
              containerPort: 3306
              protocol: TCP
          volumeMounts:
            - name: database-volume
              mountPath: /var/lib/mysql
      volumes:
        - name: database-volume
          persistentVolumeClaim:
            claimName: database-disk
```

Skapa en secret under `Workloads`>`Secrets`>`Create`>`From YAML`
```yaml
kind: Secret
apiVersion: v1
metadata:
  name: database-secret
  namespace: qrtag
data:
  MARIADB_DATABASE: qrtag
  MARIADB_PASSWORD: <Lösenordet för din databas>
  MARIADB_ROOT_PASSWORD: <Lösenordet för din databas>
  MARIADB_USER: <Användarnamnet för din databas>
type: Opaque
```

Skapa en service under `Workloads`>`Services`>`Create Service`
```yaml
apiVersion: v1
kind: Service
metadata:
  name: qrtag-database
  namespace: qrtag
spec:
  selector:
    app: qrtag-database
  ports:
    - protocol: TCP
      port: 3306
      targetPort: 3306
```

Skapa en PersistentVolumeClaims under `Storage`>`PersistentVolumeClaims`>`Create PersistentVolumeClaims`>`Edit YAML`
```yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: database-disk
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 5Gi
```

## Frivillig PHPMyAdmin

Skapa en ny deployment under `Workloads`>`Deployments`>`Create Deployment`>`YAML view` för phpmyadmin
```yaml
kind: Deployment
apiVersion: apps/v1
metadata:
  namespace: qrtag
  labels:
    app: phpmyadmin
spec:
  replicas: 1
  selector:
    matchLabels:
      app: phpmyadmin
  template:
    metadata:
      labels:
        app: phpmyadmin
    spec:
      containers:
        - name: phpmyadmin
          image: 'bitnami/phpmyadmin:latest'
          ports:
            - containerPort: 8080
              protocol: TCP
          env:
            - name: APACHE_HTTP_PORT_NUMBER
              value: '8080'
            - name: DATABASE_HOST
              value: qrtag-database
            - name: DATABASE_PORT_NUMBER
              value: '3306'
            - name: MYSQL_ROOT_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: qrtag-secrets
                  key: DB_PASSWORD
```

Skapa en ny service under `Networking`>`Services`>`Create Service`
```yaml
kind: Service
apiVersion: v1
metadata:
  name: phpmyadmin-service
  namespace: qrtag
spec:
  ports:
    - protocol: TCP
      port: 8080
      targetPort: 8080
      nodePort: 32252
  type: NodePort
  selector:
    app: phpmyadmin
```

Skapa en ny route under `Networking`>`Routes`>`Create Route`>`YAML view`
```yaml
kind: Route
apiVersion: route.openshift.io/v1
metadata:
  name: phpmyadmin
  namespace: qrtag
spec:
  to:
    kind: Service
    name: phpmyadmin-service
    weight: 100
  port:
    targetPort: 8080
```

## Grattis

Nu borde alltid funka som det ska
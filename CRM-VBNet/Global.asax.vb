' =============================================
' Global.asax.vb - Inicialización de la aplicación
' =============================================
Imports System.Web.Mvc
Imports System.Web.Routing
Imports CRM_VBNet.Models

Namespace CRM_VBNet

    Public Class MvcApplication
        Inherits System.Web.HttpApplication

        Protected Sub Application_Start()
            AreaRegistration.RegisterAllAreas()
            RouteConfig.RegisterRoutes(RouteTable.Routes)

            ' Inicializar base de datos con admin por defecto
            Try
                Dim db As New DatabaseContext(
                    System.Configuration.ConfigurationManager.ConnectionStrings("CRMConnection").ConnectionString)
                db.SeedDefaultAdmin()
            Catch ex As Exception
                ' Log del error si la BD no está disponible aún
                System.Diagnostics.Debug.WriteLine("DB init error: " & ex.Message)
            End Try
        End Sub

    End Class

End Namespace
